<?php
/**
 * Plugin Name: Al Baraka Payment Gateway for WooCommerce
 * Plugin URI: https://github.com/your-repo/albaraka-payment-gateway
 * Description: Integrates Al Baraka Payment Gateway with WooCommerce.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Ensure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // You could add an admin notice here if you want to inform the user
    // add_action( 'admin_notices', 'albaraka_woocommerce_not_active_notice' );
    return;
}
// function albaraka_woocommerce_not_active_notice() {
//    echo '<div class="error"><p>' . __( 'Al Baraka Payment Gateway requires WooCommerce to be activated.', 'albaraka-payment-gateway' ) . '</p></div>';
// }


add_action( 'plugins_loaded', 'init_wc_gateway_albaraka_plugin' );

function init_wc_gateway_albaraka_plugin() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    class WC_Gateway_Albaraka extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'albaraka_payment';
            $this->icon               = apply_filters( 'woocommerce_albaraka_icon', '' );
            $this->has_fields         = false; // No custom fields on checkout page, settings are in admin
            $this->method_title       = __( 'Al Baraka Payment Gateway', 'albaraka-payment-gateway' );
            $this->method_description = __( 'Pay with Al Baraka Payment Gateway.', 'albaraka-payment-gateway' );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings(); // This loads settings from DB into $this->settings

            // Define user-facing properties from settings.
            $this->title        = $this->get_option( 'title', __( 'Al Baraka Payment', 'albaraka-payment-gateway' ) );
            $this->description  = $this->get_option( 'description', __( 'Pay securely using Al Baraka Payment Gateway.', 'albaraka-payment-gateway' ) );
            // 'enabled' is checked by WooCommerce core

            // Al Baraka specific settings - these will be populated by init_settings() if saved, or use defaults from form_fields
            $this->testmode     = 'yes' === $this->get_option( 'testmode', 'no' );
            $this->pspId                  = $this->get_option( 'pspId' );
            $this->mpiId                  = $this->get_option( 'mpiId' );
            $this->cardAcceptor           = $this->get_option( 'cardAcceptor' );
            $this->mcc                    = $this->get_option( 'mcc' );
            $this->merchantKitId          = $this->get_option( 'merchantKitId' );
            $this->authenticationToken    = $this->get_option( 'authenticationToken' );
            $this->currency               = $this->get_option( 'currency', 'TRY' );
            $this->transactionTypeIndicator = $this->get_option( 'transactionTypeIndicator', 'S' );
            $this->redirectBackURL        = $this->get_option( 'redirectBackURL' );
            $this->callbackURL            = $this->get_option( 'callbackURL', WC()->api_request_url( 'wc_gateway_albaraka_callback' ) );
            $this->payment_url            = $this->get_option( 'payment_url' );
            $this->language               = $this->get_option( 'language', 'TR' );


            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page_handler' ) );

            // Callback for Al Baraka
            add_action( 'woocommerce_api_wc_gateway_albaraka_callback', array( $this, 'handle_albaraka_callback' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'albaraka-payment-gateway' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable Al Baraka Payment Gateway', 'albaraka-payment-gateway' ),
                    'default' => 'no',
                ),
                'title' => array(
                    'title'       => __( 'Title', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'albaraka-payment-gateway' ),
                    'default'     => __( 'Al Baraka Payment', 'albaraka-payment-gateway' ),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __( 'Description', 'albaraka-payment-gateway' ),
                    'type'        => 'textarea',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'albaraka-payment-gateway' ),
                    'default'     => __( 'Pay securely using Al Baraka Payment Gateway.', 'albaraka-payment-gateway' ),
                ),
                'testmode' => array(
                    'title'       => __( 'Test mode', 'albaraka-payment-gateway' ),
                    'type'        => 'checkbox',
                    'label'       => __( 'Enable Test Mode', 'albaraka-payment-gateway' ),
                    'default'     => 'no',
                    'description' => __( 'Place the payment gateway in test mode using test API credentials.', 'albaraka-payment-gateway' ),
                    'desc_tip'    => true,
                ),
                'pspId' => array(
                    'title'       => __( 'PSP ID', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Enter your Al Baraka PSP ID.', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'mpiId' => array(
                    'title'       => __( 'MPI ID', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Enter your Al Baraka MPI ID.', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'cardAcceptor' => array(
                    'title'       => __( 'Card Acceptor Name', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Enter your Card Acceptor Name.', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'mcc' => array(
                    'title'       => __( 'MCC (Merchant Category Code)', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Enter your Merchant Category Code.', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'merchantKitId' => array(
                    'title'       => __( 'Merchant Kit ID', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Enter your Merchant Kit ID.', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'authenticationToken' => array(
                    'title'       => __( 'Authentication Token', 'albaraka-payment-gateway' ),
                    'type'        => 'password',
                    'description' => __( 'Enter your Al Baraka Authentication Token.', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'currency' => array(
                    'title'       => __( 'Currency Code', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'Currency code for transactions (e.g., TRY, USD). Default is TRY.', 'albaraka-payment-gateway' ),
                    'default'     => 'TRY',
                    'desc_tip'    => true,
                ),
                'transactionTypeIndicator' => array(
                    'title'       => __( 'Transaction Type Indicator', 'albaraka-payment-gateway' ),
                    'type'        => 'select',
                    'options'     => array(
                        'S' => __( 'Sale (S)', 'albaraka-payment-gateway' ),
                        'A' => __( 'Authorization (A)', 'albaraka-payment-gateway' ),
                    ),
                    'default'     => 'S',
                    'description' => __( 'Select the transaction type. Default is Sale.', 'albaraka-payment-gateway' ),
                    'desc_tip'    => true,
                ),
                'redirectBackURL' => array(
                    'title'       => __( 'Redirect Back URL', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'URL to redirect the customer back to after payment attempt. If empty, WooCommerce default will be used.', 'albaraka-payment-gateway' ),
                    'default'     => '', // Let merchant define this or use WC default.
                    'desc_tip'    => true,
                ),
                'callbackURL' => array(
                    'title'       => __( 'Callback URL', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'URL for Al Baraka to send payment status updates. This is automatically generated. Provide this to Al Baraka.', 'albaraka-payment-gateway' ),
                    'default'     => WC()->api_request_url( 'wc_gateway_albaraka_callback' ),
                    'desc_tip'    => true,
                    'custom_attributes' => array( 'readonly' => 'readonly' ),
                ),
                'payment_url' => array(
                    'title'       => __( 'Al Baraka Payment URL', 'albaraka-payment-gateway' ),
                    'type'        => 'text',
                    'description' => __( 'The base URL for Al Baraka\'s payment processing page (e.g., https://payment.albaraka.com.tr/PayEntry).', 'albaraka-payment-gateway' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'language' => array(
                    'title'       => __( 'Language', 'albaraka-payment-gateway' ),
                    'type'        => 'select',
                    'options'     => array(
                        'TR' => __( 'Turkish (TR)', 'albaraka-payment-gateway' ),
                        'EN' => __( 'English (EN)', 'albaraka-payment-gateway' ),
                    ),
                    'default'     => 'TR',
                    'description' => __( 'Language for the Al Baraka payment page. Default is Turkish.', 'albaraka-payment-gateway' ),
                    'desc_tip'    => true,
                ),
            );
        }

        /**
         * Initializes settings by overriding the parent `init_settings` to ensure
         * class members are populated after settings are loaded.
         * Note: init_settings() is called by the constructor.
         * We don't need to explicitly call parent::init_settings() as it's called by WC_Settings_API::init_settings(),
         * which is what $this->init_settings() in our constructor eventually calls.
         * This function is mostly for populating our class properties from the loaded settings.
         */
        public function init_settings() {
            parent::init_settings(); // This will load saved settings into $this->settings

            // After parent::init_settings() has run, $this->settings is populated.
            // Now, assign these to our class properties.
            // The get_option method handles defaults if the setting isn't present.
            $this->enabled                  = $this->get_option( 'enabled' );
            $this->title                    = $this->get_option( 'title' );
            $this->description              = $this->get_option( 'description' );
            $this->testmode                 = 'yes' === $this->get_option( 'testmode', 'no' );
            $this->pspId                    = $this->get_option( 'pspId' );
            $this->mpiId                    = $this->get_option( 'mpiId' );
            $this->cardAcceptor             = $this->get_option( 'cardAcceptor' );
            $this->mcc                      = $this->get_option( 'mcc' );
            $this->merchantKitId            = $this->get_option( 'merchantKitId' );
            $this->authenticationToken      = $this->get_option( 'authenticationToken' );
            $this->currency                 = $this->get_option( 'currency', 'TRY' );
            $this->transactionTypeIndicator = $this->get_option( 'transactionTypeIndicator', 'S' );
            $this->redirectBackURL          = $this->get_option( 'redirectBackURL' );
            // Ensure callback URL is always correctly generated if empty or not yet saved.
            $this->callbackURL              = $this->get_option( 'callbackURL', WC()->api_request_url( 'wc_gateway_albaraka_callback' ) );
            $this->payment_url              = $this->get_option( 'payment_url' );
            $this->language                 = $this->get_option( 'language', 'TR' );
        }


        /**
         * Process the payment and return the result.
         * Placeholder for now.
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            if ( ! $order ) {
                wc_add_notice( __( 'Order not found.', 'albaraka-payment-gateway' ), 'error' );
                return array(
                    'result'   => 'failure',
                    'redirect' => wc_get_checkout_url(),
                );
            }

            if ( empty( $this->payment_url ) ) {
                wc_add_notice( __( 'Payment URL is not configured. Please contact the site administrator.', 'albaraka-payment-gateway' ), 'error' );
                return array(
                    'result'   => 'failure',
                    'redirect' => wc_get_checkout_url(),
                );
            }

            // Product description
            $product_description_parts = array();
            foreach ( $order->get_items() as $item ) {
                $product_description_parts[] = $item->get_name() . ' x ' . $item->get_quantity();
            }
            $product_description = implode( '; ', $product_description_parts );
            // Ensure description length is within Al Baraka limits if any (e.g., truncate)
            // $product_description = substr($product_description, 0, 255); // Example truncation

            // Amount: Format as required by Al Baraka (e.g., no thousand separator, specific decimal places)
            // Assuming Al Baraka requires amount with two decimal places, without thousand separator.
            $amount = number_format( $order->get_total(), 2, '.', '' );

            // redirectBackURL: Use configured one, or default to order received page.
            $redirect_back_url = ! empty( $this->redirectBackURL ) ? $this->redirectBackURL : $this->get_return_url( $order );

            $payment_args = array(
                'pspId'                     => $this->pspId,
                'mpiId'                     => $this->mpiId,
                'cardAcceptor'              => $this->cardAcceptor,
                'mcc'                       => $this->mcc,
                'merchantKitId'             => $this->merchantKitId,
                // 'authenticationToken'    => $this->authenticationToken, // Token might be used server-to-server or specific ways, not always in form
                'currency'                  => $this->currency,
                'transactionTypeIndicator'  => $this->transactionTypeIndicator,
                'redirectBackURL'           => $redirect_back_url,
                'callbackURL'               => $this->callbackURL,
                'language'                  => $this->language,
                'orderId'                   => $order->get_order_number(), // Or $order_id, depending on Al Baraka's requirement
                'amount'                    => $amount,
                'customerEmail'             => $order->get_billing_email(),
                'customerName'              => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'customerPhone'             => $order->get_billing_phone(),
                'customerAddress'           => trim( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() ),
                'customerCity'              => $order->get_billing_city(),
                'customerCountry'           => $order->get_billing_country(),
                'customerIp'                => WC_Geolocation::get_ip_address(),
                'transactionTime'           => gmdate('YmdHis'), // Format: YYYYMMDDHHMMSS
                'productDescription'        => $product_description,
                // TODO: HASH CALCULATION - CRITICAL FOR SECURITY
                // The following is a placeholder. The actual fields and hashing algorithm
                // (e.g., SHA256, MD5) must be obtained from Al Baraka's documentation.
                // Typically, you concatenate specific fields in a defined order with a secret key and then hash.
                // Example: $hash_string = $this->pspId . $order_id . $amount . $this->currency . $secret_key;
                //          $generated_hash = hash('sha256', $hash_string);
                'hash'                      => 'PLEASE_IMPLEMENT_REAL_HASH_CALCULATION', // Placeholder
            );

            // Some gateways require specific field names, e.g. some use 'clientid' instead of 'pspId'
            // or 'oid' for orderId. These need to be confirmed from Al Baraka PDF.

            $form_html = '<form action="' . esc_url( $this->payment_url ) . '" method="post" id="albaraka_payment_form" name="albaraka_payment_form" target="_self">';
            foreach ( $payment_args as $key => $value ) {
                if (is_array($value)) { // Should not happen with current args, but good practice
                    foreach ($value as $sub_value) {
                         $form_html .= '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $sub_value ) . '" />';
                    }
                } else {
                    $form_html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
                }
            }
            $form_html .= wp_nonce_field('albaraka_payment_nonce', '_wpnonce', true, false); // Add nonce
            $form_html .= '<input type="submit" class="button alt" id="albaraka_submit_button" value="' . __( 'Proceed to Al Baraka', 'albaraka-payment-gateway' ) . '" />';
            $form_html .= '<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'albaraka-payment-gateway' ) . '</a>';
            $form_html .= '</form>';
            $form_html .= '<script type="text/javascript">document.getElementById("albaraka_payment_form").submit();</script>';

            // Option 1: Return form HTML in 'redirect' field (less common for direct POST)
            // This often involves creating a temporary page that contains the form and JS.
            // WooCommerce will then try to redirect to this "URL" which is actually HTML.
            // Some browsers might block this or show warnings.

            // Option 2: Use wc_enqueue_js for the submit script and return the form.
            // This is cleaner if WooCommerce can render the form on an intermediate page.
            // wc_enqueue_js( 'document.getElementById("albaraka_payment_form").submit();' );
            // return array(
            // 'result' => 'success',
            // 'form' => $form_fields // A function that returns the form fields string
            // );
            // Then use `woocommerce_gateway_redirect_to_form` filter or similar.

            // Option 3: Directly echo the form and exit.
            // This is a very common pattern for gateways that POST directly from checkout.
            // However, it means the 'redirect' URL in the return array is not strictly used for the Al Baraka redirect.
            // It becomes a fallback or a place for WC to mark the order as pending.

            // For this implementation, we will build the form and effectively "redirect" by outputting it.
            // WooCommerce expects a 'redirect' URL. We can give it the order received page,
            // but the user will be taken to Al Baraka first.

            // To display the form and auto-submit, we can use a custom redirect page or filter `woocommerce_checkout_redirect_to_order_pay`.
            // A simpler way that works with many setups is to pass the form itself.
            // WC will output this if it's in the 'redirect' field for some gateways, but it's not standard.
            // A more robust method is to use `wc_create_order_action` or similar to store form, then redirect to a page that renders and submits it.

            // For this subtask, let's stick to the specified return structure,
            // and the form will be part of a page that the 'redirect' points to.
            // This means we need a way to pass this form HTML to that page.
            // A common way is to store it in the WC session and retrieve it on the redirect page.

            // However, the prompt implies generating the form directly.
            // If we return HTML in 'redirect', WC tries to literally redirect to a URL that *is* HTML, which is wrong.
            // The most straightforward way for an auto-submitting form when `process_payment` must return an array
            // with 'redirect' is to make 'redirect' a URL to a page that *then* displays and submits the form.
            // Or, for some gateways, they allow returning 'form' HTML.
            // Let's try returning the form directly, as some custom gateways handle this.
            // WooCommerce itself doesn't directly render HTML from the 'redirect' key.
            // The standard way to do this is to have the 'redirect' URL be an endpoint that then shows the form.
            //
            // Given the constraints, let's provide the form in a way that it can be outputted.
            // A common pattern is to have an intermediate redirect page.
            // Let's generate a unique redirect URL that will render our form.

            // Store form HTML in session to be retrieved on a new redirect page
            WC()->session->set( 'albaraka_payment_form_html', $form_html );

            // Create a temporary redirect URL that will render the form.
            // This endpoint needs to be handled by another hook, e.g., 'template_redirect'.
            // Or, more simply, redirect to the order-pay page and hook into it to display the form.
            $redirect_url = add_query_arg(
                array(
                    'albaraka_payment_process' => 'true',
                    'order_id' => $order_id,
                    '_wpnonce' => wp_create_nonce( 'albaraka_process_payment_redirect_'. $order_id )
                ),
                $order->get_checkout_payment_url( true ) // Use the order pay URL
            );

            // The `woocommerce_before_thankyou` or `woocommerce_order_details_before_order_table`
            // on the order-pay page can then be used to retrieve and display the form from session
            // and add the JS to auto-submit.

            // For the purpose of this subtask, and assuming the environment can handle it,
            // many simpler integrations directly output the form and JS.
            // This is often done by returning the 'redirect' as the current checkout page
            // with special query args, and then a hook on 'template_redirect' checks for these args,
            // prints the form and exits. This bypasses the normal WC redirect flow.

            // Let's simplify and assume the 'redirect' key can be used to pass the form for JS submission.
            // This is non-standard but sometimes implemented.
            // A better way is to return 'success' and an empty 'redirect', then use a filter
            // like `apply_filters( 'woocommerce_payment_gateway_form_fields_html', $html, $this->id );`
            // or `woocommerce_before_checkout_form` actions if `has_fields` was true.
            // Since `has_fields` is false, we are in a redirect scenario.

            // The most direct way to achieve an auto-submitting form when `process_payment` must return
            // a 'redirect' URL is that this URL itself should serve the auto-submitting form.
            // This means we need to create an endpoint or a page that does this.

            // Let's use the `woocommerce_thankyou_order_id` action to output the form
            // if the redirect goes to the thank you page. This is a common workaround.
            // The 'redirect' will be the standard thank you page.
            // We store the form in session and print it on the thank you page.

            // Add a transient to signal the thankyou_page_handler to output the form
            set_transient( 'albaraka_form_for_order_' . $order_id, $form_html, MINUTE_IN_SECONDS * 5 );


            return array(
                'result'   => 'success',
                // Redirect to the order-pay page. We'll hook into this page to output the form.
                // Or, if Albaraka's redirectBackURL is reliable and they handle display, we might go there.
                // For now, let's use the standard WooCommerce order received page.
                'redirect' => $this->get_return_url( $order )
            );
        }

        /**
         * Placeholder for thank you page handler.
         * This function is called on the thank you page.
         * We will use it to output and auto-submit the form to Al Baraka.
         * Note: $order_id is passed to this hook.
         */
        public function thankyou_page_handler( $order_id ) {
            $form_html = get_transient( 'albaraka_form_for_order_' . $order_id );
            if ( $form_html ) {
                // Clear the transient
                delete_transient( 'albaraka_form_for_order_' . $order_id );
                // Output the form. Ensure this is done before any other significant output on the thank you page.
                // This might require a hook that fires very early on the thank you page, or careful management of output.
                // A common issue is headers already sent if not handled correctly.
                // For simplicity in this example, we echo it.
                // In a real plugin, you might buffer output or use a more specific hook.
                echo $form_html; // Ensure no other HTML is echoed before this if it needs to be a clean POST page.
                // It's generally better to redirect to a dedicated page that ONLY has this form.
                // However, using the thank you page is a common shortcut.
            }
            // Original thank you page content will follow unless exit() is called after echo.
        }

        /**
         * Handle the callback from Al Baraka.
         * This is where Al Baraka will send POST/JSON requests to update order status.
         */
        public function handle_albaraka_callback() {
            $logger = wc_get_logger();
            $raw_post_data = file_get_contents( 'php://input' );

            $logger->info( 'Al Baraka Callback Triggered. Raw Data: ' . $raw_post_data, array( 'source' => 'albaraka-payment-gateway' ) );
            $logger->info( 'Al Baraka Callback POST Data: ' . print_r( $_POST, true ), array( 'source' => 'albaraka-payment-gateway' ) );


            $data = array();
            $order_id_key = 'idTransaction'; // Default assumption, might be 'orderId' or other from POST
            $transaction_stat_key = 'transactionStat';
            $transaction_id_key = 'idTransaction'; // This might be a different Al Baraka specific transaction ID

            if ( ! empty( $raw_post_data ) ) {
                $json_data = json_decode( $raw_post_data, true );
                if ( json_last_error() === JSON_ERROR_NONE && is_array( $json_data ) ) {
                    $data = $json_data;
                    $logger->info( 'Callback data parsed as JSON.', array( 'source' => 'albaraka-payment-gateway' ) );
                    // If JSON, keys are likely fixed as per API spec, e.g. 'idTransaction', 'transactionStat'
                    // The prompt specifically mentions 'idTransaction' and 'transactionStat' from JSON.
                } else {
                     $logger->warning( 'Callback data is not valid JSON or not an array. Last JSON error: ' . json_last_error_msg(), array( 'source' => 'albaraka-payment-gateway' ) );
                    // Fallback to POST if JSON parsing failed or was empty but POST might exist
                    if (!empty($_POST)) {
                        $data = $_POST;
                        $logger->info( 'Callback data parsed from POST.', array( 'source' => 'albaraka-payment-gateway' ) );
                    } else {
                        $logger->error( 'Callback data is empty or could not be parsed (JSON or POST).', array( 'source' => 'albaraka-payment-gateway' ) );
                        echo 'Error: No data received or invalid format.';
                        exit;
                    }
                }
            } elseif (!empty($_POST)) {
                 $data = $_POST;
                 $logger->info( 'Callback data parsed from POST as raw_post_data was empty.', array( 'source' => 'albaraka-payment-gateway' ) );
            } else {
                $logger->error( 'Al Baraka Callback: No data received (empty JSON body and POST).', array( 'source' => 'albaraka-payment-gateway' ) );
                echo 'Error: No data received.';
                exit;
            }

            // Sanitize data (example for top-level keys)
            $data = array_map( 'sanitize_text_field', $data ); // Basic sanitization

            $order_id_val = isset( $data[$order_id_key] ) ? $data[$order_id_key] : null;
            // Sometimes the order ID might be in a different field in POST vs JSON, or under a generic name.
            // For example, if your form sent 'orderId' as the WooCommerce order number.
            if (empty($order_id_val) && isset($data['orderId'])) { // Check alternative common name
                $order_id_val = $data['orderId'];
            }

            $transaction_stat = isset( $data[$transaction_stat_key] ) ? $data[$transaction_stat_key] : null;
            $transaction_id = isset( $data[$transaction_id_key] ) ? $data[$transaction_id_key] : null; // This might be Al Baraka's own transaction ref

            if ( ! $order_id_val ) {
                $logger->error( 'Al Baraka Callback: Order ID not found in callback data. Searched for key: ' . $order_id_key . ' and orderId.', array( 'source' => 'albaraka-payment-gateway' ) );
                echo 'Error: Order ID missing.';
                exit;
            }

            $order_id = absint( $order_id_val );
            $order    = wc_get_order( $order_id );

            if ( ! $order ) {
                $logger->error( 'Al Baraka Callback: Order not found with ID: ' . $order_id, array( 'source' => 'albaraka-payment-gateway' ) );
                // Use __() for user-facing error messages if this echo was ever shown, though 'OK' is typical for callbacks.
                echo esc_html__( 'Error: Order not found.', 'albaraka-payment-gateway' );
                exit;
            }

            // TODO: CRITICAL SECURITY CHECK - Implement Hash/Signature Verification
            // This is a placeholder. You MUST verify the callback authenticity using the method
            // specified by Al Baraka (e.g., comparing a received hash with a locally generated one
            // using your secret key, or validating a token).
            // Example:
            // $secret_key = $this->authenticationToken; // Or another dedicated secret for callback
            // $calculated_hash = generate_albaraka_callback_hash($data, $secret_key); // Implement this function
            // $received_hash = isset($data['hash']) ? $data['hash'] : ''; // Or the relevant hash field from Al Baraka
            // if ( !hash_equals($calculated_hash, $received_hash) ) {
            //    $logger->error( 'Al Baraka Callback: Hash mismatch. Order ID: ' . $order_id, array( 'source' => 'albaraka-payment-gateway' ) );
            //    echo esc_html__( 'Error: Security check failed.', 'albaraka-payment-gateway' );
            //    exit;
            // }
            $logger->warning( 'Al Baraka Callback: SECURITY CHECK PLACEHOLDER - Implement hash/signature verification. Order ID: ' . $order_id, array( 'source' => 'albaraka-payment-gateway' ) );


            if ( $order->is_paid() || $order->has_status( array( 'processing', 'completed' ) ) ) {
                 $logger->info( 'Al Baraka Callback: Order ' . $order_id . ' already processed. Current status: ' . $order->get_status(), array( 'source' => 'albaraka-payment-gateway' ) );
                 echo 'OK'; // Acknowledge, but don't reprocess
                 exit;
            }

            // Explicitly sanitize the specific data pieces being used in notes or comparisons.
            $sanitized_transaction_id = sanitize_text_field( $transaction_id );
            $sanitized_transaction_stat = sanitize_text_field( $transaction_stat );

            // Process based on transactionStat - values 'S', 'F', 'C' are examples
            // These need to be confirmed from Al Baraka documentation.
            switch ( strtoupper( $sanitized_transaction_stat ) ) { // Using strtoupper for case-insensitivity
                case 'S': // Assuming 'S' means Successful
                case 'SUCCESS': // Common alternative
                case 'APPROVED': // Common alternative
                    $order->payment_complete( $sanitized_transaction_id ); // Pass Al Baraka's transaction ID if available and distinct
                    $order->add_order_note(
                        sprintf(
                            __( 'Al Baraka payment successful.%1$sTransaction ID (Al Baraka): %2$s%1$sTransaction Status from Gateway: %3$s', 'albaraka-payment-gateway' ),
                            "\n",
                            $sanitized_transaction_id,
                            $sanitized_transaction_stat
                        )
                    );
                    // wc_reduce_stock_levels($order_id); // payment_complete() usually handles this.
                    $logger->info( 'Al Baraka Callback: Payment completed for order ' . $order_id . '. Al Baraka Transaction ID: ' . $sanitized_transaction_id, array( 'source' => 'albaraka-payment-gateway' ) );
                    break;
                case 'F': // Assuming 'F' means Failed
                case 'FAIL': // Common alternative
                case 'FAILED':
                case 'C': // Assuming 'C' means Cancelled
                case 'CANCELLED':
                case 'DECLINED':
                    $order->update_status( 'failed',
                        sprintf(
                            __( 'Al Baraka payment failed/cancelled.%1$sTransaction ID (Al Baraka): %2$s%1$sTransaction Status from Gateway: %3$s', 'albaraka-payment-gateway' ),
                            "\n",
                            $sanitized_transaction_id,
                            $sanitized_transaction_stat
                        )
                    );
                    $logger->info( 'Al Baraka Callback: Payment failed/cancelled for order ' . $order_id . '. Status: ' . $sanitized_transaction_stat . '. Al Baraka Transaction ID: ' . $sanitized_transaction_id, array( 'source' => 'albaraka-payment-gateway' ) );
                    break;
                default:
                    $order->add_order_note(
                        sprintf(
                            __( 'Al Baraka payment returned with an unhandled status.%1$sTransaction ID (Al Baraka): %2$s%1$sTransaction Status from Gateway: %3$s', 'albaraka-payment-gateway' ),
                            "\n",
                            $sanitized_transaction_id,
                            $sanitized_transaction_stat
                        )
                    );
                    $logger->warning( 'Al Baraka Callback: Unhandled payment status for order ' . $order_id . '. Status: ' . $sanitized_transaction_stat . '. Al Baraka Transaction ID: ' . $sanitized_transaction_id, array( 'source' => 'albaraka-payment-gateway' ) );
                    break;
            }

            echo 'OK'; // Acknowledge receipt to Al Baraka
            exit;
        }
    }
}

/**
 * Add Al Baraka Gateway to WooCommerce list of payment gateways.
 *
 * @param array $gateways All available WC gateways.
 * @return array Filtered list of WC gateways.
 */
function add_albaraka_gateway_to_woocommerce( $gateways ) {
    $gateways[] = 'WC_Gateway_Albaraka';
    return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'add_albaraka_gateway_to_woocommerce' );

/**
 * Plugin activation function.
 * This function is called when the plugin is activated.
 * It can be used to set default options or perform other setup tasks.
 */
function albaraka_payment_gateway_activate() {
    // Ensure settings are correctly initialized if they don't exist.
    // WooCommerce usually handles default settings well when the settings page for the gateway is first visited.
    // However, explicitly setting them can be a good practice.
    if ( class_exists('WC_Gateway_Albaraka') && is_admin() ) {
        $gateway = new WC_Gateway_Albaraka();
        $options = get_option( $gateway->get_option_key(), array() );

        if ( empty( $options ) ) {
            // If no settings exist, populate with defaults from form fields
            $default_settings = array();
            foreach ( $gateway->get_form_fields() as $key => $field ) {
                if ( isset( $field['default'] ) ) {
                    $default_settings[$key] = $field['default'];
                }
            }
            update_option( $gateway->get_option_key(), $default_settings );
        }
    }
}
register_activation_hook( __FILE__, 'albaraka_payment_gateway_activate' );

/**
 * Add settings link on plugin page for easier access.
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function albaraka_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=albaraka_payment' ) . '">' . __( 'Settings', 'albaraka-payment-gateway' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'albaraka_plugin_action_links' );

/**
 * Load plugin textdomain for internationalization.
 */
// function albaraka_load_my_plugin_textdomain() {
//    load_plugin_textdomain( 'albaraka-payment-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
// }
// add_action( 'plugins_loaded', 'albaraka_load_my_plugin_textdomain' );

?>
