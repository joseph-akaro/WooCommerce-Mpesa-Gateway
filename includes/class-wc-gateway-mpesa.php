<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * WC_Gateway_Mpesa Class.
 */
class WC_Gateway_Mpesa extends WC_Payment_Gateway
{
    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id                 = 'mpesa';
        
        // Define the icon with responsive styling
        $icon_url = apply_filters('woocommerce_mpesa_icon_url', plugins_url('../assets/images/mpesa-logo.png', __FILE__));
        $icon_style = 'max-width: 80px; height: auto; vertical-align: middle; margin-left: 10px;';
        $this->icon = '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr__('M-Pesa Logo', 'woocommerce-mpesa') . '" style="' . esc_attr($icon_style) . '" />';

        $this->has_fields         = true; // This is crucial for adding the phone number field
        $this->method_title       = __('M-Pesa', 'woocommerce-mpesa');
        $this->method_description = __('Enable M-Pesa STK Push payments for your store.', 'woocommerce-mpesa');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->instructions = $this->get_option('instructions', $this->description);
        $this->enabled      = $this->get_option('enabled');
        $this->testmode     = 'yes' === $this->get_option('testmode');

        // Actions.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
    }

    /**
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = include 'settings-mpesa.php';
    }

    /**
     * Add the phone number field to the checkout page.
     */
    public function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }

        echo '<fieldset id="wc-' . esc_attr($this->id) . '-form" class="wc-payment-form" style="background:transparent;">';
        do_action('woocommerce_credit_card_form_start', $this->id);

        echo '<div class="form-row form-row-wide">
                <label for="mpesa_phone_number">' . esc_html__('M-Pesa Phone Number', 'woocommerce-mpesa') . '&nbsp;<span class="required">*</span></label>
                <input id="mpesa_phone_number" name="mpesa_phone_number" type="tel" autocomplete="tel" placeholder="' . esc_attr__('e.g. 0712345678', 'woocommerce-mpesa') . '">
              </div>';

        do_action('woocommerce_credit_card_form_end', $this->id);
        echo '<div class="clear"></div></fieldset>';
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // Check if the phone number was entered
        if (empty($_POST['mpesa_phone_number'])) {
            wc_add_notice(__('Please enter your M-Pesa phone number to proceed.', 'woocommerce-mpesa'), 'error');
            return;
        }

        // Sanitize the phone number and format it to the required 254... format
        $phone = sanitize_text_field($_POST['mpesa_phone_number']);
        $phone = '254' . substr(preg_replace('/[^0-9]/', '', $phone), -9);

        // Save the phone number to the order for reference
        $order->update_meta_data('_mpesa_phone_number', $phone);
        $order->add_order_note('Customer M-Pesa number: ' . $phone);
        $order->save();

        // Include the API handler class.
        require_once plugin_dir_path(__FILE__) . 'class-mpesa-api.php';

        // Initiate the STK push.
        $mpesa_api = new Mpesa_API($this);
        $response  = $mpesa_api->initiate_stk_push($order, $phone);

        if (is_wp_error($response)) {
            wc_add_notice($response->get_error_message(), 'error');
            return;
        }

        // Mark as on-hold (we're awaiting the callback).
        $order->update_status('on-hold', __('Awaiting M-Pesa payment confirmation.', 'woocommerce-mpesa'));

        // Reduce stock levels.
        wc_reduce_stock_levels($order_id);

        // Remove cart.
        WC()->cart->empty_cart();

        // Return thankyou redirect.
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    /**
     * Output for the order received page.
     *
     * @param int $order_id Order ID.
     */
    public function thankyou_page($order_id)
    {
        if ($this->instructions) {
            echo wpautop(wptexturize(wp_kses_post($this->instructions)));
        }
    }

    /**
     * Get the transaction URL.
     *
     * @param  WC_Order $order Order object.
     * @return string
     */
    public function get_transaction_url($order)
    {
        if ($this->testmode) {
            $this->view_transaction_url = 'https://sandbox.safaricom.co.ke/v2/transaction-status/index';
        } else {
            $this->view_transaction_url = 'https://safaricom.co.ke/v2/transaction-status/index';
        }

        return parent::get_transaction_url($order);
    }
}
