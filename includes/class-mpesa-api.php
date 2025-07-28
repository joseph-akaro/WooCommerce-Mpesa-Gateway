<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Mpesa_API Class.
 */
class Mpesa_API
{
    private $gateway;

    public function __construct($gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Get the access token.
     *
     * @return string|WP_Error
     */
    private function get_access_token()
    {
        // Check for cached token first
        $cached_token = get_transient('mpesa_access_token');
        if ($cached_token) {
            return $cached_token;
        }

        $url = $this->gateway->testmode ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $consumer_key    = $this->gateway->get_option('consumer_key');
        $consumer_secret = $this->gateway->get_option('consumer_secret');

        $response = wp_remote_get(
            $url,
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($consumer_key . ':' . $consumer_secret),
                ),
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error('mpesa_api_error', __('Could not connect to M-Pesa to get access token.', 'woocommerce-mpesa'));
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        if (isset($body->access_token)) {
            set_transient('mpesa_access_token', $body->access_token, 3500); // Cache for 58 minutes
            return $body->access_token;
        }

        return new WP_Error('mpesa_api_error', __('Could not retrieve access token.', 'woocommerce-mpesa'));
    }

    /**
     * Initiate STK push.
     *
     * @param WC_Order $order Order object.
     * @param string   $phone Phone number.
     * @return array|WP_Error
     */
    public function initiate_stk_push($order, $phone)
    {
        $access_token = $this->get_access_token();

        if (is_wp_error($access_token)) {
            return $access_token;
        }

        $url = $this->gateway->testmode ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $timestamp = date('YmdHis');
        $shortcode = $this->gateway->get_option('shortcode');
        $passkey   = $this->gateway->get_option('passkey');
        $password  = base64_encode($shortcode . $passkey . $timestamp);
        
        // This will correctly use your live domain URL.
        $callback_url = home_url('/wc-api/WC_Gateway_Mpesa/');

        $payload = array(
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => round($order->get_total()),
            'PartyA'            => $phone,
            'PartyB'            => $shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $callback_url,
            'AccountReference'  => $order->get_order_key(),
            'TransactionDesc'   => 'Payment for order ' . $order->get_order_number(),
        );

        $response = wp_remote_post(
            $url,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => json_encode($payload),
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error('mpesa_api_error', __('Could not connect to M-Pesa for STK push.', 'woocommerce-mpesa'));
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        if (isset($body->ResponseCode) && '0' === $body->ResponseCode) {
            // Save the MerchantRequestID to the order. This is crucial for matching the callback.
            $order->update_meta_data('_mpesa_merchant_request_id', $body->MerchantRequestID);
            $order->save();
            return $body;
        }

        return new WP_Error('mpesa_api_error', isset($body->errorMessage) ? $body->errorMessage : __('An error occurred while initiating the payment.', 'woocommerce-mpesa'));
    }

    /**
     * Handle the callback from M-Pesa.
     */
    public static function handle_callback()
    {
        $callback_data = file_get_contents('php://input');
        $callback_data = json_decode($callback_data);

        if (isset($callback_data->Body->stkCallback)) {
            $stk_callback = $callback_data->Body->stkCallback;
            
            $merchant_request_id = $stk_callback->MerchantRequestID;
            $checkout_request_id = $stk_callback->CheckoutRequestID;

            // Find the order using the MerchantRequestID
            global $wpdb;
            $order_id = $wpdb->get_var($wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_mpesa_merchant_request_id' AND meta_value = %s", 
                $merchant_request_id
            ));

            if ($order_id) {
                $order = wc_get_order($order_id);

                if ($order && $order->get_status() === 'on-hold') {
                    if ($stk_callback->ResultCode == 0) {
                        // Payment was successful
                        $mpesa_receipt_number = 'N/A';
                        if (isset($stk_callback->CallbackMetadata->Item)) {
                            foreach ($stk_callback->CallbackMetadata->Item as $item) {
                                if (isset($item->Name) && $item->Name === 'MpesaReceiptNumber') {
                                    $mpesa_receipt_number = $item->Value;
                                }
                            }
                        }
                        $order->payment_complete($mpesa_receipt_number);
                        $order->add_order_note(sprintf(__('M-Pesa payment successful. Receipt Number: %s', 'woocommerce-mpesa'), $mpesa_receipt_number));
                    } else {
                        // Payment failed or was cancelled
                        $order->update_status('failed', sprintf(__('M-Pesa payment failed. Reason: %s', 'woocommerce-mpesa'), $stk_callback->ResultDesc));
                    }
                }
            }
        }

        // Respond to Safaricom to acknowledge receipt of the callback.
        header('Content-Type: application/json');
        echo json_encode(
            array(
                'ResultCode' => 0,
                'ResultDesc' => 'Accepted',
            )
        );
        exit;
    }
}
