<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. Register custom routing query parameters with WordPress
 */
add_filter('query_vars', 'velvet_seo_register_query_vars');
function velvet_seo_register_query_vars($vars) {
    $vars[] = 'velvet_robots';
    $vars[] = 'velvet_sitemap';
    return $vars;
}

/**
 * 2. Add structural rewriting intercepts into the engine rules array
 */
add_action('init', 'velvet_seo_add_rewrite_rules');
function velvet_seo_add_rewrite_rules() {
    // Intercepts /robots.txt
    add_rewrite_rule('^robots\.txt$', 'index.php?velvet_robots=1', 'top');
    // Intercepts /sitemap.xml
    add_rewrite_rule('^sitemap\.xml$', 'index.php?velvet_sitemap=1', 'top');
}

/**
 * 3. Force Flush rewrite configurations safely during plugin activation
 */
function velvet_seo_flush_rewrite_rules_safety() {
    velvet_seo_add_rewrite_rules();
    flush_rewrite_rules();
}

/**
 * 4. Parse incoming query tracks and override content generation templates
 */
add_action('template_redirect', 'velvet_seo_handle_dynamic_requests');
function velvet_seo_handle_dynamic_requests() {
    // A. Handle Dynamic Robots.txt Rendering
    if (get_query_var('velvet_robots') == 1) {
        //$robots_enable = get_option('velvet_seo_robots_enable', 'yes');
        $robots_enable = get_option('velvet_seo_robots_txt_enable', 'no');

        if ($robots_enable !== 'yes') {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            exit;
        }

        $robots_content = get_option(
            'velvet_seo_robots_txt_content',
            "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php"
        );

        header('Content-Type: text/plain; charset=utf-8');
        echo $robots_content;
        exit;
    }

    // B. Handle Dynamic XML Sitemap Processing Node Loop
    if (get_query_var('velvet_sitemap') == 1) {
        $sitemap_enable = get_option('velvet_seo_sitemap_enable', 'yes');
        
        if ($sitemap_enable !== 'yes') {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            get_template_part('404');
            exit;
        }

        $include_posts = get_option('velvet_seo_sitemap_include_posts', 'yes');
        $include_pages = get_option('velvet_seo_sitemap_include_pages', 'yes');

        // Compile allowed post types array
        $post_types = array();
        if ($include_posts === 'yes') $post_types[] = 'post';
        if ($include_pages === 'yes') $post_types[] = 'page';

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Output Home Directory URI
        echo '<url>';
        echo '<loc>' . esc_url(home_url('/')) . '</loc>';
        echo '<changefreq>daily</changefreq>';
        echo '<priority>1.0</priority>';
        echo '</url>';

        // 2. Query valid live node items from DB
        if (!empty($post_types)) {
            $query_args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => 100, // Safe payload boundary array limit
                'orderby'        => 'modified',
                'order'          => 'DESC'
            );
            
            $sitemap_posts = get_posts($query_args);
            foreach ($sitemap_posts as $post) {
                echo '<url>';
                echo '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>';
                echo '<lastmod>' . esc_html(get_the_modified_date('Y-m-d\TH:i:sP', $post->ID)) . '</lastmod>';
                echo '<changefreq>weekly</changefreq>';
                echo '<priority>0.6</priority>';
                echo '</url>';
            }
        }

        echo '</urlset>';
        exit;
    }
}