<?php
if (!defined('ABSPATH')) exit;

/**
 * Get all default configurations for Velvet SEO
 */
function velvet_seo_get_defaults() {
    return array(
        'page_title_enable'       => 'no',
        'page_title_val'          => '',
        'meta_desc_enable'        => 'no',
        'meta_desc_val'           => '',
        'meta_key_enable'         => 'no',
        'meta_key_val'            => '',
        'canonical_enable'        => 'no',
        'canonical_val'           => '',
        'meta_robots_enable'      => 'no',
        'meta_robots_val'         => 'index, follow',
        'theme_color_enable'      => 'no',
        'theme_color_val'         => '#000000',
        'author_enable'           => 'no',
        'author_val'              => '',
        'og_enable'               => 'no',
        'og_title'                => '',
        'og_description'          => '',
        'og_image'                => '',
        'og_url'                  => '',
        'twitter_enable'          => 'no',
        'twitter_title'           => '',
        'twitter_description'     => '',
        'twitter_image'           => '',
        'schema_enable'           => 'no',
        'schema_json'             => '',
        'gtm_enable'              => 'no',
        'gtm_id'                  => '',
        'google_verification_enable' => 'no',
        'google_verification_tag'    => ''
    );
}

/**
 * Safely fetch plugin settings merged with system defaults
 */
function velvet_seo_get_settings() {
    $saved = get_option('velvet_seo_settings', array());
    $defaults = velvet_seo_get_defaults();
    return wp_parse_args($saved, $defaults);
}