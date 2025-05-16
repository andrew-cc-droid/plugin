<?php
/**
 * Handles API interactions for the WOPEN-OS Presale
 */
class WOPEN_OS_Presale_API {
    
    /**
     * Create a new order in the database
     * 
     * @param float $amount WOPEN amount
     * @param string $os_address ChainOS destination address
     * @param int $user_id WordPress user ID (optional)
     * @return array|WP_Error Order data or error
     */
    public static function create_order($amount, $os_address, $user_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        
        // Validate inputs
        if (!is_numeric($amount) || floatval($amount) <= 0) {
            return new WP_Error('invalid_amount', __('Invalid amount specified', 'wopen-os-presale'));
        }
        
        if (empty($os_address)) {
            return new WP_Error('invalid_address', __('Invalid OS address specified', 'wopen-os-presale'));
        }
        
        // Get current rates
        $current_rate = floatval(get_option('wopen_os_presale_rate', 0.1));
        $rate_increase_time = intval(get_option('wopen_os_rate_increase_time', time() + (12 * 60 * 60)));
        
        // Determine which rate to use
        $rate = time() < $rate_increase_time ? $current_rate : floatval(get_option('wopen_os_next_rate', 0.02));
        
        // Calculate token amount
        $token_amount = $amount * $rate;
        
        // Generate Solana keypair
        require_once WOPEN_OS_PRESALE_PATH . 'includes/class-wopen-os-presale-solana.php';
        $keypair = WOPEN_OS_Presale_Solana::generate_keypair();
        
        // Generate unique access identifier
        $access_identifier = WOPEN_OS_Presale_Solana::generate_access_identifier();
        
        // Check if identifier already exists
        while ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE access_identifier = %s", $access_identifier)) > 0) {
            $access_identifier = WOPEN_OS_Presale_Solana::generate_access_identifier();
        }
        
        // Prepare order data
        $order_data = array(
            'user_id' => $user_id,
            'amount' => $amount,
            'token_amount' => $token_amount,
            'wopen_address' => $keypair['publicKey'],
            'os_address' => $os_address,
            'order_status' => 'pending',
            'solana_keypair' => json_encode($keypair),
            'access_identifier' => $access_identifier,
            'created_at' => current_time('mysql')
        );
        
        // Insert order to database
        $result = $wpdb->insert($table_name, $order_data);
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create order in database', 'wopen-os-presale'));
        }
        
        // Get the inserted ID
        $order_id = $wpdb->insert_id;
        
        // Return success with order data
        return array(
            'success' => true,
            'order_id' => $order_id,
            'deposit_address' => $keypair['publicKey'],
            'access_identifier' => $access_identifier,
            'amount' => $amount,
            'token_amount' => $token_amount,
            'os_address' => $os_address
        );
    }
    
    /**
     * Get order by access identifier
     * 
     * @param string $access_identifier Unique access identifier
     * @return array|WP_Error Order data or error
     */
    public static function get_order_by_identifier($access_identifier) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        
        // Query the database
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE access_identifier = %s",
            $access_identifier
        ), ARRAY_A);
        
        if (empty($order)) {
            return new WP_Error('not_found', __('Order not found with the provided access identifier', 'wopen-os-presale'));
        }
        
        return $order;
    }
    
    /**
     * Update order status
     * 
     * @param int $order_id Order ID
     * @param string $status New status
     * @param array $additional_data Additional data to update
     * @return bool|WP_Error Success or error
     */
    public static function update_order_status($order_id, $status, $additional_data = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        
        // Validate status
        $valid_statuses = array('pending', 'processing', 'completed', 'failed');
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', __('Invalid order status', 'wopen-os-presale'));
        }
        
        // Prepare update data
        $update_data = array_merge(
            array('order_status' => $status),
            $additional_data
        );
        
        // Update the database
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $order_id)
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update order status', 'wopen-os-presale'));
        }
        
        return true;
    }
    
    /**
     * Check transaction status (simplified for demo)
     * In production, this would connect to blockchain nodes
     * 
     * @param string $deposit_address Deposit address to check
     * @param float $expected_amount Expected amount
     * @return array Transaction status data
     */
    public static function check_transaction_status($deposit_address, $expected_amount) {
        // For demo purposes, randomly decide if transaction is found
        $transaction_found = (rand(0, 10) > 3); // 70% chance of finding a transaction
        
        if (!$transaction_found) {
            return array(
                'success' => true,
                'found' => false,
                'message' => __('No transaction found yet. Please send WOPEN tokens to the deposit address.', 'wopen-os-presale')
            );
        }
        
        // Generate a fake transaction
        $tx_hash = 'tx_' . bin2hex(random_bytes(16));
        
        return array(
            'success' => true,
            'found' => true,
            'transaction' => array(
                'hash' => $tx_hash,
                'amount' => $expected_amount,
                'status' => 'confirmed',
                'timestamp' => time()
            ),
            'message' => __('Transaction found and confirmed!', 'wopen-os-presale')
        );
    }
}
