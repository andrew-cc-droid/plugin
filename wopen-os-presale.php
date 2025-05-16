<?php
/**
 * Plugin Name: WOPEN-OS Presale
 * Plugin URI: https://chainos.network/
 * Description: Cross-chain presale between Solana's WOPEN token and ChainOS's OS token with countdown and rate change.
 * Version: 1.0.0
 * Author: ChainOS Team
 * Author URI: https://chainos.network/
 * Text Domain: wopen-os-presale
 * Domain Path: /languages
 * License: GPL-2.0+
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define plugin constants
 */
define('WOPEN_OS_PRESALE_VERSION', '1.0.0');
define('WOPEN_OS_PRESALE_PATH', plugin_dir_path(__FILE__));
define('WOPEN_OS_PRESALE_URL', plugin_dir_url(__FILE__));
define('WOPEN_OS_PRESALE_BASENAME', plugin_basename(__FILE__));

/**
 * Use unique prefix to avoid conflicts with other plugins
 */
class WOPEN_OS_Presale {

    /**
     * Instance of this class
     */
    protected static $instance = null;

    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Load dependencies
        $this->load_dependencies();

        // Register all hooks
        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        // Register shortcode
        add_shortcode('wopen_os_presale', array($this, 'presale_shortcode'));
    }

    /**
     * Singleton instance
     */
    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Admin functions
        require_once WOPEN_OS_PRESALE_PATH . 'admin/class-wopen-os-presale-admin.php';
        
        // Public functions
        require_once WOPEN_OS_PRESALE_PATH . 'public/class-wopen-os-presale-public.php';
        
        // Core plugin functionality
        require_once WOPEN_OS_PRESALE_PATH . 'includes/class-wopen-os-presale-solana.php';
        require_once WOPEN_OS_PRESALE_PATH . 'includes/class-wopen-os-presale-api.php';
    }

    /**
     * Register admin hooks
     */
    private function define_admin_hooks() {
        $plugin_admin = new WOPEN_OS_Presale_Admin();
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        
        // Admin menu
        add_action('admin_menu', array($plugin_admin, 'add_menu_pages'));
        
        // Settings
        add_action('admin_init', array($plugin_admin, 'register_settings'));
    }

    /**
     * Register public hooks
     */
    private function define_public_hooks() {
        $plugin_public = new WOPEN_OS_Presale_Public();
        
        // Public scripts and styles
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_wopen_os_generate_address', array($plugin_public, 'generate_deposit_address'));
        add_action('wp_ajax_nopriv_wopen_os_generate_address', array($plugin_public, 'generate_deposit_address'));
    }

    /**
     * Shortcode callback for presale page
     */
    public function presale_shortcode($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(array(
            'title' => 'WOPEN-OS Presale',
        ), $atts);
        
        // Load template
        ob_start();
        include WOPEN_OS_PRESALE_PATH . 'public/partials/presale-display.php';
        return ob_get_clean();
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        // Create database tables
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'wopen_os_orders';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NULL,
            amount decimal(18,8) NOT NULL,
            token_amount decimal(18,8) NOT NULL,
            wopen_address varchar(255) NOT NULL,
            os_address varchar(255) NOT NULL,
            order_status varchar(50) NOT NULL DEFAULT 'pending',
            solana_keypair longtext NOT NULL,
            access_identifier varchar(20) NOT NULL,
            transaction_hash varchar(255) NULL,
            chainos_transaction_hash varchar(255) NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY access_identifier (access_identifier)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $options = array(
            'wopen_os_presale_rate' => 0.1, // 1 WOPEN = 0.1 OS
            'wopen_os_next_rate' => 0.02, // Rate after countdown
            'wopen_os_rate_increase_time' => time() + (12 * 60 * 60), // 12 hours from now
            'wopen_os_wopen_contract' => '9KvS8EevsAK8kh8JVfFxbN5V58HHAxx8A6V6a4bqpump',
            'wopen_os_os_contract' => 'chainos154l9u8pqz5uevarvupe9xpfh9dfg2xx7y5sstn'
        );
        
        foreach ($options as $key => $value) {
            if (!get_option($key)) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Clean up database if needed
        // Be careful not to remove important user data
    }
}

// Plugin activation/deactivation hooks
register_activation_hook(__FILE__, array('WOPEN_OS_Presale', 'activate'));
register_deactivation_hook(__FILE__, array('WOPEN_OS_Presale', 'deactivate'));

// Initialize the plugin
function run_wopen_os_presale() {
    return WOPEN_OS_Presale::get_instance();
}

// Start the plugin
run_wopen_os_presale();
