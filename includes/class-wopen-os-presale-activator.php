<?php
/**
 * Handles plugin activation redirect
 */

class WOPEN_OS_Presale_Activator {
    
    /**
     * Initialize activation hooks
     */
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'activation_redirect'));
    }
    
    /**
     * Redirect to the presale page after activation
     */
    public static function activation_redirect() {
        // Check if we should redirect
        if (get_option('wopen_os_presale_activation_redirect', false)) {
            // Delete the redirect option
            delete_option('wopen_os_presale_activation_redirect');
            
            // Get the page ID
            $page_id = get_option('wopen_os_presale_page_id');
            
            if ($page_id) {
                // Get the permalink
                $permalink = get_permalink($page_id);
                
                if ($permalink) {
                    // Redirect to the presale page
                    wp_redirect($permalink);
                    exit;
                }
            }
        }
    }
}

// Initialize
WOPEN_OS_Presale_Activator::init();
