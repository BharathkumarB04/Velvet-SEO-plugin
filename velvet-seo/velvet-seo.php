<?php
/**
 * Plugin Name: Velvet SEO
 * Plugin URI:  https://github.com/BharathkumarB04/Velvet-SEO-plugin
 * Description: A modern, lightweight, and professional WordPress SEO plugin to manage meta tags, schema, and social sharing. One Stop solution for SEO.!!
 * Version:     1.0
 * Author:      Bharathkumar
 * Text Domain: velvet-seo
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define('VELVET_SEO_PATH', plugin_dir_path(__FILE__));
define('VELVET_SEO_URL', plugin_dir_url(__FILE__));

// Include Required Files
require_once VELVET_SEO_PATH . 'includes/helpers.php';
require_once VELVET_SEO_PATH . 'includes/frontend-meta.php';
require_once VELVET_SEO_PATH . 'includes/post-seo-meta.php';
require_once VELVET_SEO_PATH . 'includes/seo-assets-generator.php';

// Load Text Domain for Translation Support
add_action('plugins_loaded', 'velvet_seo_load_textdomain');
function velvet_seo_load_textdomain() {
    load_plugin_textdomain('velvet-seo', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Add Admin Menu Hook
add_action('admin_menu', 'velvet_seo_add_admin_menu');
function velvet_seo_add_admin_menu() {
    // 1. Create Main Parent Menu
    add_menu_page(
        __('Velvet SEO Dashboard', 'velvet-seo'),
        __('Velvet SEO', 'velvet-seo'),
        'manage_options',
        'velvet-seo',
        'velvet_seo_render_settings_page',
        'dashicons-chart-line',
        30
    );

    // 2. Main Dashboard Submenu (Matches the parent slug to clean up namespacing)
    add_submenu_page(
        'velvet-seo',
        __('Global Settings', 'velvet-seo'),
        __('Global Settings', 'velvet-seo'),
        'manage_options',
        'velvet-seo',
        'velvet_seo_render_settings_page'
    );

    // 3. New Submenu: Robots Txt Controller
    add_submenu_page(
        'velvet-seo',
        __('Robots Txt Manager', 'velvet-seo'),
        __('Robots.txt Manager', 'velvet-seo'),
        'manage_options',
        'velvet-seo-robots',
        'velvet_seo_render_robots_page'
    );

    // 4. New Submenu: XML Sitemap Generator
    add_submenu_page(
        'velvet-seo',
        __('Sitemap Manager', 'velvet-seo'),
        __('Sitemap Manager', 'velvet-seo'),
        'manage_options',
        'velvet-seo-sitemap',
        'velvet_seo_render_sitemap_page'
    );
}

// Render Settings Page Wrapper
function velvet_seo_render_settings_page() {
    require_once VELVET_SEO_PATH . 'admin/settings-page.php';
}

// Render Robots Settings Page
function velvet_seo_render_robots_page() {
    if (file_exists(VELVET_SEO_PATH . 'admin/robots-page.php')) {
        require_once VELVET_SEO_PATH . 'admin/robots-page.php';
    } else {
        echo '<div class="wrap"><h1>' . __('Robots.txt Configuration', 'velvet-seo') . '</h1><p>' . __('Module UI file missing. Create admin/robots-page.php.', 'velvet-seo') . '</p></div>';
    }
}

// Render Sitemap Settings Page
function velvet_seo_render_sitemap_page() {
    if (file_exists(VELVET_SEO_PATH . 'admin/sitemap-page.php')) {
        require_once VELVET_SEO_PATH . 'admin/sitemap-page.php';
    } else {
        echo '<div class="wrap"><h1>' . __('XML Sitemap Generator', 'velvet-seo') . '</h1><p>' . __('Module UI file missing. Create admin/sitemap-page.php.', 'velvet-seo') . '</p></div>';
    }
}

// Enqueue Admin Scripts and Styles
add_action('admin_enqueue_scripts', 'velvet_seo_enqueue_admin_assets');
function velvet_seo_enqueue_admin_assets($hook) {
    // Array of your plugin hooks to ensure scripts run everywhere in your submenus
    $allowed_hooks = array(
        'toplevel_page_velvet-seo',
        'velvet-seo_page_velvet-seo-robots',
        'velvet-seo_page_velvet-seo-sitemap'
    );

    if (!in_array($hook, $allowed_hooks)) {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('velvet-seo-admin-css', VELVET_SEO_URL . 'admin/admin.css', array(), '1.0.0');
    wp_enqueue_script('velvet-seo-admin-js', VELVET_SEO_URL . 'admin/admin.js', array('jquery', 'wp-color-picker'), '1.0.0', true);
}

// Core Plugin Activation Safety Trigger Matrix
register_activation_hook(__FILE__, 'velvet_seo_plugin_activation_routine');
function velvet_seo_plugin_activation_routine() {
    // Include required asset generators to unlock setup functions
    require_once VELVET_SEO_PATH . 'includes/seo-assets-generator.php';
    require_once VELVET_SEO_PATH . 'includes/helpers.php';

    // 1. Flush rewrite rules cleanly for your custom sitemaps architecture
    if (function_exists('velvet_seo_flush_rewrite_rules_safety')) {
        velvet_seo_flush_rewrite_rules_safety();
    }

    // 2. Initialize Options Array with Empty Fallbacks if missing to completely kill PHP Undefined Index notices
    $existing_options = get_option('velvet_seo_settings');
    if (false === $existing_options) {
        $default_options = function_exists('velvet_seo_get_defaults') ? velvet_seo_get_defaults() : array();
        
        // Ensure new verification structures are tracked seamlessly out-of-the-box
        $default_options['google_verification_enable'] = 'no';
        $default_options['google_verification_tag']    = '';
        
        update_option('velvet_seo_settings', $default_options);
    }
}

// Virtual robots.txt handling
add_filter('robots_txt', 'velvet_seo_virtual_robots_handler', 9999, 2);
function velvet_seo_virtual_robots_handler($output, $public) {
    // 1. Check if the user turned the custom virtual override toggle to "yes"
    $is_enabled = get_option('velvet_seo_robots_txt_enable', 'no');
    
    if ($is_enabled !== 'yes') {
        return $output; // Pass through original/default WordPress rules if disabled
    }

    // 2. Return user configurations directly 
    $custom_directives = get_option('velvet_seo_robots_txt_content', '');
    
    if (!empty($custom_directives)) {
        return $custom_directives;
    }

    return $output;
}
