<?php
/**
 * Public-facing functionality of the plugin
 */
class WOPEN_OS_Presale_Public {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        // Nothing to do here yet
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wopen-os-presale-public',
            WOPEN_OS_PRESALE_URL . 'public/css/wopen-os-presale-public.css',
            array(),
            WOPEN_OS_PRESALE_VERSION,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site
     */
    public function enqueue_scripts() {
        $countdown_time = get_option('wopen_os_rate_increase_time', time() + (12 * 60 * 60));
        
        wp_enqueue_script(
            'wopen-os-presale-public',
            WOPEN_OS_PRESALE_URL . 'public/js/wopen-os-presale-public.js',
            array('jquery'),
            WOPEN_OS_PRESALE_VERSION,
            false
        );
        
        // Pass variables to JS
        wp_localize_script(
            'wopen-os-presale-public',
            'wopen_os_presale',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wopen-os-presale-nonce'),
                'countdown_time' => $countdown_time * 1000, // Convert to milliseconds
                'current_time' => time() * 1000, // Current time in milliseconds
                'current_rate' => floatval(get_option('wopen_os_presale_rate', 0.1)),
                'next_rate' => floatval(get_option('wopen_os_next_rate', 0.02)),
                'wopen_contract' => get_option('wopen_os_wopen_contract', '9KvS8EevsAK8kh8JVfFxbN5V58HHAxx8A6V6a4bqpump'),
                'os_contract' => get_option('wopen_os_os_contract', 'chainos154l9u8pqz5uevarvupe9xpfh9dfg2xx7y5sstn'),
                'is_logged_in' => is_user_logged_in() ? 1 : 0,
                'texts' => array(
                    'deposit_address_generated' => __('Your unique deposit address has been generated.', 'wopen-os-presale'),
                    'save_identifier_warning' => __('IMPORTANT: Save this access identifier! It\'s required to access your staking account.', 'wopen-os-presale'),
                    'amount_required' => __('Please enter a valid WOPEN amount.', 'wopen-os-presale'),
                    'address_required' => __('Please enter your ChainOS wallet address.', 'wopen-os-presale'),
                    'generating_address' => __('Generating address...', 'wopen-os-presale'),
                    'generate_address' => __('Generate Deposit Address', 'wopen-os-presale'),
                    'hours' => __('Hours', 'wopen-os-presale'),
                    'minutes' => __('Minutes', 'wopen-os-presale'),
                    'seconds' => __('Seconds', 'wopen-os-presale'),
                    'current_rate' => __('Current Rate', 'wopen-os-presale'),
                    'after_countdown' => __('After Countdown', 'wopen-os-presale'),
                    'get_more_tokens' => __('Get 5x more tokens by participating now!', 'wopen-os-presale'),
                    'price_increases' => __('Price increases in:', 'wopen-os-presale'),
                    'limited_time_offer' => __('LIMITED TIME OFFER', 'wopen-os-presale')
                )
            )
        );
    }
    
    /**
     * Ajax handler to generate a deposit address
     */
    public function generate_deposit_address() {
        // Check nonce
        check_ajax_referer('wopen-os-presale-nonce', 'nonce');
        
        // Get parameters
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $os_address = isset($_POST['os_address']) ? sanitize_text_field($_POST['os_address']) : '';
        
        // Validate inputs
        if ($amount <= 0) {
            wp_send_json_error(array(
                'message' => __('Please enter a valid amount of WOPEN tokens.', 'wopen-os-presale')
            ));
            return;
        }
        
        if (empty($os_address)) {
            wp_send_json_error(array(
                'message' => __('Please enter your ChainOS wallet address.', 'wopen-os-presale')
            ));
            return;
        }
        
        // Get current user if logged in
        $user_id = is_user_logged_in() ? get_current_user_id() : null;
        
        // Create order in database
        require_once WOPEN_OS_PRESALE_PATH . 'includes/class-wopen-os-presale-api.php';
        $result = WOPEN_OS_Presale_API::create_order($amount, $os_address, $user_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message()
            ));
            return;
        }
        
        // Return success
        wp_send_json_success(array(
            'order_id' => $result['order_id'],
            'deposit_address' => $result['deposit_address'],
            'access_identifier' => $result['access_identifier'],
            'amount' => $result['amount'],
            'token_amount' => $result['token_amount'],
            'message' => sprintf(
                __('Your deposit address has been generated. Send %s WOPEN to the address below to receive %s OS tokens.', 'wopen-os-presale'),
                number_format($result['amount'], 8),
                number_format($result['token_amount'], 8)
            )
        ));
    }
    
    /**
     * Ajax handler to check transaction status
     */
    public function check_transaction_status() {
        // Check nonce
        check_ajax_referer('wopen-os-presale-nonce', 'nonce');
        
        // Get parameters
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $access_identifier = isset($_POST['access_identifier']) ? sanitize_text_field($_POST['access_identifier']) : '';
        
        if ($order_id <= 0 && empty($access_identifier)) {
            wp_send_json_error(array(
                'message' => __('Invalid order information.', 'wopen-os-presale')
            ));
            return;
        }
        
        // Get order
        global $wpdb;
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        
        if (!empty($access_identifier)) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE access_identifier = %s",
                $access_identifier
            ), ARRAY_A);
        } else {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $order_id
            ), ARRAY_A);
        }
        
        if (empty($order)) {
            wp_send_json_error(array(
                'message' => __('Order not found.', 'wopen-os-presale')
            ));
            return;
        }
        
        // Check transaction status
        require_once WOPEN_OS_PRESALE_PATH . 'includes/class-wopen-os-presale-api.php';
        $status = WOPEN_OS_Presale_API::check_transaction_status(
            $order['wopen_address'],
            $order['amount']
        );
        
        // Update order if transaction found
        if ($status['found'] && empty($order['transaction_hash'])) {
            WOPEN_OS_Presale_API::update_order_status(
                $order['id'],
                'processing',
                array('transaction_hash' => $status['transaction']['hash'])
            );
            
            // In a real implementation, this would trigger a process to send OS tokens
            // and later update the order with chainos_transaction_hash and status=completed
        }
        
        // Return status
        wp_send_json_success(array(
            'order' => array(
                'id' => $order['id'],
                'status' => $order['order_status'],
                'amount' => $order['amount'],
                'token_amount' => $order['token_amount'],
                'wopen_address' => $order['wopen_address'],
                'os_address' => $order['os_address'],
                'transaction_hash' => $order['transaction_hash'],
                'chainos_transaction_hash' => $order['chainos_transaction_hash']
            ),
            'transaction' => $status['found'] ? $status['transaction'] : null,
            'message' => $status['message']
        ));
    }
}
