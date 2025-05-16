<?php
/**
 * Admin-specific functionality for WOPEN-OS Presale
 */
class WOPEN_OS_Presale_Admin {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        // Nothing to do here yet
    }
    
    /**
     * Register admin styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wopen-os-presale-admin', 
            WOPEN_OS_PRESALE_URL . 'admin/css/wopen-os-presale-admin.css', 
            array(), 
            WOPEN_OS_PRESALE_VERSION, 
            'all'
        );
    }
    
    /**
     * Register admin scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wopen-os-presale-admin', 
            WOPEN_OS_PRESALE_URL . 'admin/js/wopen-os-presale-admin.js', 
            array('jquery'), 
            WOPEN_OS_PRESALE_VERSION, 
            false
        );
    }
    
    /**
     * Register admin menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'WOPEN-OS Presale',
            'WOPEN-OS Presale',
            'manage_options',
            'wopen-os-presale',
            array($this, 'display_dashboard'),
            'dashicons-chart-area',
            25
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wopen-os-presale',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'wopen-os-presale',
            array($this, 'display_dashboard')
        );
        
        // Orders submenu
        add_submenu_page(
            'wopen-os-presale',
            'Orders',
            'Orders',
            'manage_options',
            'wopen-os-presale-orders',
            array($this, 'display_orders')
        );
        
        // Settings submenu
        add_submenu_page(
            'wopen-os-presale',
            'Settings',
            'Settings',
            'manage_options',
            'wopen-os-presale-settings',
            array($this, 'display_settings')
        );
    }
    
    /**
     * Register admin initialization hooks
     * This ensures menu and settings are registered properly
     */
    public function admin_init() {
        // Force refresh of menu cache
        delete_transient('menu');
        
        // Register settings
        register_setting('wopen_os_presale_settings', 'wopen_os_presale_rate');
        register_setting('wopen_os_presale_settings', 'wopen_os_next_rate');
        register_setting('wopen_os_presale_settings', 'wopen_os_rate_increase_time');
        register_setting('wopen_os_presale_settings', 'wopen_os_wopen_contract');
        register_setting('wopen_os_presale_settings', 'wopen_os_os_contract');
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General settings section
        add_settings_section(
            'wopen_os_presale_general_section',
            __('Presale Settings', 'wopen-os-presale'),
            array($this, 'render_general_section'),
            'wopen_os_presale_general'
        );
        
        // Rate field
        add_settings_field(
            'wopen_os_presale_rate',
            __('Current Rate (WOPEN to OS)', 'wopen-os-presale'),
            array($this, 'render_rate_field'),
            'wopen_os_presale_general',
            'wopen_os_presale_general_section'
        );
        
        // Next rate field
        add_settings_field(
            'wopen_os_next_rate',
            __('Rate After Countdown (WOPEN to OS)', 'wopen-os-presale'),
            array($this, 'render_next_rate_field'),
            'wopen_os_presale_general',
            'wopen_os_presale_general_section'
        );
        
        // Rate increase time field
        add_settings_field(
            'wopen_os_rate_increase_time',
            __('Countdown End Time', 'wopen-os-presale'),
            array($this, 'render_rate_increase_time_field'),
            'wopen_os_presale_general',
            'wopen_os_presale_general_section'
        );
        
        // WOPEN contract field
        add_settings_field(
            'wopen_os_wopen_contract',
            __('WOPEN Contract Address', 'wopen-os-presale'),
            array($this, 'render_wopen_contract_field'),
            'wopen_os_presale_general',
            'wopen_os_presale_general_section'
        );
        
        // OS contract field
        add_settings_field(
            'wopen_os_os_contract',
            __('OS Contract Address', 'wopen-os-presale'),
            array($this, 'render_os_contract_field'),
            'wopen_os_presale_general',
            'wopen_os_presale_general_section'
        );
    }
    
    /**
     * Render general section
     */
    public function render_general_section() {
        echo '<p>' . __('Configure your WOPEN to OS presale settings.', 'wopen-os-presale') . '</p>';
    }
    
    /**
     * Render rate field
     */
    public function render_rate_field() {
        $value = get_option('wopen_os_presale_rate', 0.1);
        echo '<input type="number" step="0.0001" min="0.0001" name="wopen_os_presale_rate" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('The exchange rate from WOPEN to OS (e.g., 0.1 means 1 WOPEN = 0.1 OS or 100,000 WOPEN = 10,000 OS)', 'wopen-os-presale') . '</p>';
    }
    
    /**
     * Render next rate field
     */
    public function render_next_rate_field() {
        $value = get_option('wopen_os_next_rate', 0.02);
        echo '<input type="number" step="0.0001" min="0.0001" name="wopen_os_next_rate" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('The exchange rate after countdown ends (e.g., 0.02 means 1 WOPEN = 0.02 OS or 100,000 WOPEN = 2,000 OS)', 'wopen-os-presale') . '</p>';
    }
    
    /**
     * Render rate increase time field
     */
    public function render_rate_increase_time_field() {
        $timestamp = get_option('wopen_os_rate_increase_time', time() + (12 * 60 * 60));
        $date = date('Y-m-d\TH:i', $timestamp);
        echo '<input type="datetime-local" name="wopen_os_rate_increase_time" value="' . esc_attr($date) . '" class="regular-text" />';
        echo '<p class="description">' . __('When the countdown ends and the rate changes', 'wopen-os-presale') . '</p>';
    }
    
    /**
     * Render WOPEN contract field
     */
    public function render_wopen_contract_field() {
        $value = get_option('wopen_os_wopen_contract', '9KvS8EevsAK8kh8JVfFxbN5V58HHAxx8A6V6a4bqpump');
        echo '<input type="text" name="wopen_os_wopen_contract" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Solana WOPEN token contract address', 'wopen-os-presale') . '</p>';
    }
    
    /**
     * Render OS contract field
     */
    public function render_os_contract_field() {
        $value = get_option('wopen_os_os_contract', 'chainos154l9u8pqz5uevarvupe9xpfh9dfg2xx7y5sstn');
        echo '<input type="text" name="wopen_os_os_contract" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('ChainOS OS token contract address', 'wopen-os-presale') . '</p>';
    }
    
    /**
     * Display dashboard page
     */
    public function display_dashboard() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get presale statistics
        global $wpdb;
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        
        // Get total orders
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Get total amount deposited
        $total_wopen = $wpdb->get_var("SELECT SUM(amount) FROM $table_name");
        
        // Get total OS tokens to be distributed
        $total_os = $wpdb->get_var("SELECT SUM(token_amount) FROM $table_name");
        
        // Show dashboard
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="wopen-os-dashboard-cards">
                <div class="wopen-os-card">
                    <h2><?php _e('Total Orders', 'wopen-os-presale'); ?></h2>
                    <p class="wopen-os-big-number"><?php echo esc_html($total_orders ? $total_orders : '0'); ?></p>
                </div>
                
                <div class="wopen-os-card">
                    <h2><?php _e('Total WOPEN Deposited', 'wopen-os-presale'); ?></h2>
                    <p class="wopen-os-big-number"><?php echo esc_html($total_wopen ? number_format($total_wopen, 2) : '0'); ?></p>
                </div>
                
                <div class="wopen-os-card">
                    <h2><?php _e('Total OS Tokens', 'wopen-os-presale'); ?></h2>
                    <p class="wopen-os-big-number"><?php echo esc_html($total_os ? number_format($total_os, 2) : '0'); ?></p>
                </div>
            </div>
            
            <div class="wopen-os-dashboard-info">
                <h2><?php _e('Presale Information', 'wopen-os-presale'); ?></h2>
                <p>
                    <strong><?php _e('Current Rate:', 'wopen-os-presale'); ?></strong>
                    1 WOPEN = <?php echo esc_html(get_option('wopen_os_presale_rate', 0.1)); ?> OS
                </p>
                <p>
                    <strong><?php _e('Rate After Countdown:', 'wopen-os-presale'); ?></strong>
                    1 WOPEN = <?php echo esc_html(get_option('wopen_os_next_rate', 0.02)); ?> OS
                </p>
                <p>
                    <strong><?php _e('Countdown End Time:', 'wopen-os-presale'); ?></strong>
                    <?php echo esc_html(date('Y-m-d H:i:s', get_option('wopen_os_rate_increase_time', time() + 12 * 3600))); ?>
                </p>
            </div>
            
            <div class="wopen-os-dashboard-links">
                <a href="<?php echo admin_url('admin.php?page=wopen-os-presale-orders'); ?>" class="button button-primary"><?php _e('View Orders', 'wopen-os-presale'); ?></a>
                <a href="<?php echo admin_url('admin.php?page=wopen-os-presale-settings'); ?>" class="button button-secondary"><?php _e('Presale Settings', 'wopen-os-presale'); ?></a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display settings page
     */
    public function display_settings() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Show settings form
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wopen_os_presale_settings');
                do_settings_sections('wopen_os_presale_general');
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2><?php _e('How to Use', 'wopen-os-presale'); ?></h2>
            <p><?php _e('Add the presale form to any page or post using this shortcode:', 'wopen-os-presale'); ?></p>
            <code>[wopen_os_presale]</code>
            <p><?php _e('Or visit the automatically created page:', 'wopen-os-presale'); ?></p>
            <a href="<?php echo esc_url(home_url('/wopen-os-presale/')); ?>" class="button button-primary" target="_blank"><?php _e('View Presale Page', 'wopen-os-presale'); ?></a>
        </div>
        <?php
    }
    
    /**
     * Display orders page
     */
    public function display_orders() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        
        // Get orders
        $orders = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
        
        // Show orders table
        ?>
        <div class="wrap">
            <h1><?php _e('WOPEN-OS Presale Orders', 'wopen-os-presale'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wopen-os-presale'); ?></th>
                        <th><?php _e('User', 'wopen-os-presale'); ?></th>
                        <th><?php _e('WOPEN Amount', 'wopen-os-presale'); ?></th>
                        <th><?php _e('OS Amount', 'wopen-os-presale'); ?></th>
                        <th><?php _e('WOPEN Address', 'wopen-os-presale'); ?></th>
                        <th><?php _e('OS Address', 'wopen-os-presale'); ?></th>
                        <th><?php _e('Access ID', 'wopen-os-presale'); ?></th>
                        <th><?php _e('Status', 'wopen-os-presale'); ?></th>
                        <th><?php _e('Created', 'wopen-os-presale'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9"><?php _e('No orders found.', 'wopen-os-presale'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo esc_html($order['id']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($order['user_id'])) {
                                        $user = get_userdata($order['user_id']);
                                        echo esc_html($user ? $user->user_login : 'Unknown');
                                    } else {
                                        echo 'Guest';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($order['amount']); ?></td>
                                <td><?php echo esc_html($order['token_amount']); ?></td>
                                <td><?php echo esc_html($order['wopen_address']); ?></td>
                                <td><?php echo esc_html($order['os_address']); ?></td>
                                <td><?php echo esc_html($order['access_identifier']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($order['order_status']); ?>">
                                        <?php echo esc_html(ucfirst($order['order_status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
