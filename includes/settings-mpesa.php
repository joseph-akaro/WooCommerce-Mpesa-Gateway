<?php

if (!defined('ABSPATH')) {
    exit;
}

return array(
    'enabled'         => array(
        'title'   => __('Enable/Disable', 'woocommerce-mpesa'),
        'type'    => 'checkbox',
        'label'   => __('Enable M-Pesa', 'woocommerce-mpesa'),
        'default' => 'yes',
    ),
    'title'           => array(
        'title'       => __('Title', 'woocommerce-mpesa'),
        'type'        => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-mpesa'),
        'default'     => __('M-Pesa', 'woocommerce-mpesa'),
        'desc_tip'    => true,
    ),
    'description'     => array(
        'title'       => __('Description', 'woocommerce-mpesa'),
        'type'        => 'textarea',
        'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-mpesa'),
        'default'     => __('Pay via M-Pesa STK Push.', 'woocommerce-mpesa'),
    ),
    'instructions'    => array(
        'title'       => __('Instructions', 'woocommerce-mpesa'),
        'type'        => 'textarea',
        'description' => __('Instructions that will be added to the thank you page.', 'woocommerce-mpesa'),
        'default'     => __('Your payment is being processed. You will receive a confirmation message shortly.', 'woocommerce-mpesa'),
    ),
    'testmode'        => array(
        'title'       => __('Test mode', 'woocommerce-mpesa'),
        'label'       => __('Enable Test Mode', 'woocommerce-mpesa'),
        'type'        => 'checkbox',
        'description' => __('Place the payment gateway in test mode using sandbox credentials.', 'woocommerce-mpesa'),
        'default'     => 'yes',
        'desc_tip'    => true,
    ),
    'consumer_key'    => array(
        'title' => __('Consumer Key', 'woocommerce-mpesa'),
        'type'  => 'text',
    ),
    'consumer_secret' => array(
        'title' => __('Consumer Secret', 'woocommerce-mpesa'),
        'type'  => 'password',
    ),
    'shortcode'       => array(
        'title' => __('Shortcode', 'woocommerce-mpesa'),
        'type'  => 'text',
    ),
    'passkey'         => array(
        'title' => __('Passkey', 'woocommerce-mpesa'),
        'type'  => 'text',
    ),
);