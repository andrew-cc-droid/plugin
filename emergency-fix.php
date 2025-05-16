<?php
/**
 * Emergency fix for WOPEN-OS Presale Plugin
 * 
 * Instructions:
 * 1. Upload this file to your WordPress root directory
 * 2. Visit this file in your browser: https://yoursite.com/emergency-fix.php
 * 3. The script will create the presale page and redirect you to it
 * 4. Delete this file after use for security
 */

// Bootstrap WordPress
require_once('wp-load.php');

// Force create the presale page
function force_create_presale_page() {
    // Delete any existing page with that slug to start fresh
    $existing = get_page_by_path('wopen-os-presale');
    if ($existing) {
        wp_delete_post($existing->ID, true);
    }
    
    // Create a new page
    $page = array(
        'post_title'    => 'WOPEN-OS Presale',
        'post_content'  => '[wopen_os_presale]',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type'     => 'page',
        'post_name'     => 'wopen-os-presale'
    );
    
    // Insert the post into the database
    $page_id = wp_insert_post($page);
    
    if ($page_id) {
        // Save the page ID in options
        update_option('wopen_os_presale_page_id', $page_id);
        
        // Force flush rewrite rules
        flush_rewrite_rules();
        
        // Return the page URL
        return get_permalink($page_id);
    }
    
    return false;
}

// Run the fix and redirect
$page_url = force_create_presale_page();

// Show result
echo '<html><head><title>WOPEN-OS Presale Emergency Fix</title></head><body>';
echo '<h1>WOPEN-OS Presale Emergency Fix</h1>';

if ($page_url) {
    echo '<p style="color:green;font-weight:bold;">✅ SUCCESS: Presale page was created successfully!</p>';
    echo '<p>Your presale page is now available at: <a href="' . esc_url($page_url) . '">' . esc_html($page_url) . '</a></p>';
    echo '<p><a href="' . esc_url($page_url) . '" style="display:inline-block;background:#0073aa;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;">Go to Presale Page</a></p>';
} else {
    echo '<p style="color:red;font-weight:bold;">❌ ERROR: Failed to create the presale page.</p>';
    echo '<p>Please check your WordPress installation and try again.</p>';
}

echo '<div style="margin-top:20px;padding:10px;background:#f8f8f8;border:1px solid #ddd;">';
echo '<p>⚠️ <strong>Important:</strong> Delete this file (emergency-fix.php) from your server for security reasons.</p>';
echo '</div>';

echo '</body></html>';
