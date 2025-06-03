# Al Baraka Payment Gateway for WooCommerce

Integrates Al Baraka Bank's payment gateway with WooCommerce, allowing you to accept payments online.

## Description

This plugin provides a payment gateway option in WooCommerce for customers to pay using Al Baraka Bank's online payment processing service. It redirects customers to the Al Baraka payment page and handles the callback to update order statuses in WooCommerce.

## Installation

1.  **Download the plugin**:
    *   You can download the plugin files directly from the [GitHub repository](https://github.com/Ghadeer1992/albaraka-payment-gateway-wp) (Clone or download ZIP).
2.  **Upload to WordPress**:
    *   Log in to your WordPress admin area.
    *   Navigate to **Plugins > Add New**.
    *   Click on **Upload Plugin**.
    *   Choose the downloaded ZIP file and click **Install Now**.
3.  **Activate the plugin**:
    *   Once installed, click **Activate Plugin**.
4.  **Configure the Gateway**:
    *   Go to **WooCommerce > Settings > Payments**.
    *   You should see "Al Baraka Payment Gateway" in the list. Click on it or its "Manage" button.
    *   Enter your Al Baraka merchant credentials (pspId, mpiId, cardAcceptor, mcc, merchantKitId, authenticationToken, etc.) as provided by Al Baraka Bank.
    *   Configure the Payment URL, Redirect Back URL, and Callback URL. The default callback URL is usually `yourdomain.com/wc-api/wc_gateway_albaraka_callback/`.
    *   Save changes.

## Configuration

All configuration settings for this gateway are located in **WooCommerce > Settings > Payments > Al Baraka Payment Gateway**.

Key fields include:
*   PSP ID
*   MPI ID
*   Card Acceptor Name
*   MCC (Merchant Category Code)
*   Merchant Kit ID
*   Authentication Token
*   Currency
*   Transaction Type Indicator
*   Redirect Back URL
*   Callback URL
*   Al Baraka Payment URL
*   Language

**Sandbox/Test Credentials:**

*   Please refer to the "Al Baraka Payment Gateway integration guide" PDF provided by Al Baraka Bank for sandbox credentials and test card numbers.
    *   *(TODO: If specific sandbox credentials become known and can be publicly shared, add them here.)*

**Important Security Note:**
The callback from Al Baraka needs to be secured. This plugin includes placeholders for hash/signature verification. Ensure you or your developer implement this security measure based on Al Baraka's specific technical documentation to prevent fraudulent transaction updates.

## Callback / Webhook URL

The default callback URL that Al Baraka will use to send payment status updates is:
`yourdomain.com/wc-api/wc_gateway_albaraka_callback/`

(Replace `yourdomain.com` with your actual domain name). You typically need to provide this URL to Al Baraka Bank during your merchant account setup.

## Advanced Configuration

For detailed information on all API fields, request/response formats, hash calculation, and advanced configuration options, please refer to the official "Al Baraka Payment Gateway integration guide" PDF provided by Al Baraka Bank.

## Support

This plugin is provided as-is. For issues related to your Al Baraka merchant account or the payment gateway service itself, please contact Al Baraka Bank support. For issues with the plugin's code, you can open an issue on the [GitHub repository](https://github.com/Ghadeer1992/albaraka-payment-gateway-wp/issues).
