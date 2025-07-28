# M-Pesa for WooCommerce
Contributors: Joseph Akaro

  Tags: woocommerce, mpesa, payment gateway, kenya, safaricom, stk push, mobile money

- Requires at least: 5.0

- Tested up to: 6.4

- WC requires at least: 8.0

- WC tested up to: 10.0.0

- Stable tag: 1.0.0

- License: GPLv2 or later

- License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple and reliable way to accept M-Pesa payments on your WooCommerce store using Safaricom's STK Push.

## Description
M-Pesa for WooCommerce allows you to seamlessly integrate Safaricom's M-Pesa payment gateway with your online store. This plugin initiates a payment prompt (STK Push) directly to the customer's phone, allowing them to authorize the transaction by simply entering their M-Pesa PIN. This eliminates the need for customers to manually enter Paybill or Till numbers, providing a fast, secure, and convenient checkout experience.

This gateway is designed for businesses operating in Kenya and other regions where M-Pesa is a primary payment method.

## Key Features
### Seamless STK Push Integration:
Initiates a payment prompt directly on the customer's mobile phone.

### Dedicated Phone Number Field:
A clear and separate field for customers to enter their M-Pesa phone number at checkout.

### Automatic Order Status Updates:
Correctly handles callbacks from Safaricom to automatically update order status from "on-hold" to "processing" or "completed".

### Live & Sandbox Modes:
Easily switch between test (sandbox) and live environments from the plugin settings.

### Secure API Authentication: 
Caches the access token for improved performance and security.

### Responsive Checkout Logo:
The M-Pesa logo is styled to look great on all devices.

## Installation
### Automatic Installation
- Log in to your WordPress dashboard.

- Navigate to Plugins > Add New.

- Search for "M-Pesa for WooCommerce".

- Click Install Now and then Activate.

## Manual Installation
- Download the plugin .zip file.

- In your WordPress admin dashboard, navigate to Plugins > Add New.

- Click the Upload Plugin button.

- Choose the .zip file you downloaded and click Install Now.

- After the plugin is installed, click Activate Plugin.

## Configuration
To configure the plugin, you need credentials from the Safaricom Developer Portal.

### Step 1:
- Get Your Safaricom API Credentials
- Go to the Safaricom Developer Portal and log in or create an account.

- Click on My Apps and create a new app.

- In your new app, select the Lipa na M-Pesa Sandbox (for testing) and Lipa na M-Pesa (for live) APIs.

- Once the app is created, you will get your Consumer Key and Consumer Secret.

- For live transactions, you will need to complete the "Go Live" process on the Safaricom portal to get your live credentials and your Business Shortcode and Passkey.

### Step 2:
- Configure the Plugin in WooCommerce
In your WordPress dashboard, navigate to WooCommerce > Settings > Payments.

- Find M-Pesa in the list and click Manage.

- Enable the gateway.

- Title & Description: Customize the text that customers will see at checkout.

- Test Mode: Check this box to use the sandbox environment for testing. Uncheck it to go live.

- Credentials: Enter your Consumer Key, Consumer Secret, Shortcode, and Passkey. Use sandbox credentials for test mode and live credentials for your live site.

- Click Save changes.

### Step 3:
- Register Callback URLs (Crucial for Live Mode)

- For your live site to receive payment confirmations, you must register your callback URL with Safaricom.

- Log in to your Safaricom Developer Portal and go to your app.

- Select the Lipa na M-Pesa Online Payment API.

- In the Register URLs section, enter your website's callback URL in both the "Confirmation URL" and "Validation URL" fields.

- Your URL is: https://your-domain.com/wc-api/WC_Gateway_Mpesa/ (replace your-domain.com with your actual domain).

- Save the changes.

## Troubleshooting
### Orders are Stuck "On-Hold"
This is the most common issue and it means your website is not receiving the confirmation callback from Safaricom.

-  `Confirm URL Registration`: Ensure you have correctly registered your callback URLs in the Safaricom app (see Step 3 above). This is the most likely cause on a live site.

- `Check Credentials`: Double-check that you are using the correct live/sandbox credentials and that "Test Mode" is set appropriately.

- `Plugin/Theme Conflict`: Temporarily disable security or caching plugins to see if they are blocking the callback.

- `Server Firewall`: Contact your hosting provider and ask them to ensure they are not blocking incoming POST requests from Safaricom's servers to your callback URL.

## Frequently Asked Questions
`Q: Does this plugin work outside of Kenya?` A: This plugin uses the Safaricom M-Pesa API, which is primarily for Kenyan businesses. However, it can process payments from any Safaricom user.

`Q: Can I accept credit cards with this plugin?` A: No, this plugin is exclusively for M-Pesa mobile money payments. To accept credit cards, you will need a separate payment gateway plugin.

## Changelog
- 1.0.0 - 2025-07-28
Initial release.

- STK Push integration.

- Dedicated phone number field at checkout.

- Responsive logo at checkout.

- Robust callback handling.