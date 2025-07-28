<?php
/**
 * Plugin Name: Stacky Media - WooCommerce Mpese
 * Plugin URI: https://josephakaro.com/woopesa
 * Description: A custom Mpesa payment gateway for WooCommerce.
 * Version: 1.0.0
 * Author: Joseph Akaro
 * Author URI: https://josephakaro.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 8.0.0
 * WC tested up to: 10.0.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Initialize the gateway.
 */
function wc_mpesa_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Include the main gateway class.
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-mpesa.php';

    /**
     * Add the gateway to WooCommerce.
     *
     * @param array $methods WooCommerce payment methods.
     * @return array
     */
    function wc_add_mpesa_gateway($methods)
    {
        $methods[] = 'WC_Gateway_Mpesa';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'wc_add_mpesa_gateway');

    // Add a link to the settings page on the plugins page.
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wc_mpesa_action_links');

    /**
     * Add settings link to the plugins page.
     *
     * @param array $links Plugin action links.
     * @return array
     */
    function wc_mpesa_action_links($links)
    {
        $settings_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=mpesa') . '">' . __('Settings', 'woocommerce-mpesa') . '</a>',
        );
        return array_merge($settings_links, $links);
    }
}

add_action('plugins_loaded', 'wc_mpesa_init', 11);

/**
 * Register the callback URL.
 */
function wc_mpesa_register_callback_url()
{
    // Include the API handler class.
    require_once plugin_dir_path(__FILE__) . 'includes/class-mpesa-api.php';
    // Register the callback endpoint.
    add_action('woocommerce_api_wc_gateway_mpesa', array('Mpesa_API', 'handle_callback'));
}

add_action('init', 'wc_mpesa_register_callback_url');
