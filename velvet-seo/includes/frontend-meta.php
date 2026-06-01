<?php
/**
 * Velvet SEO - Frontend Meta Engine
 * Handles the injection of meta tags, social cards, and tracking scripts.
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. TITLE OVERRIDE HOOK
 * Prioritizes Velvet SEO Custom Title -> Global Title -> WP Title
 */
add_filter('pre_get_document_title', 'velvet_seo_filter_wp_title', 9999);
add_filter('wp_title', 'velvet_seo_filter_wp_title', 9999);
function velvet_seo_filter_wp_title($title) {
    if (is_singular()) {
        $post_title = get_post_meta(get_the_ID(), '_velvet_post_title', true);
        if (!empty($post_title)) return esc_html($post_title);
    }

    $options = velvet_seo_get_settings();
    if (isset($options['page_title_enable']) && $options['page_title_enable'] === 'yes' && !empty($options['page_title_val'])) {
        return esc_html($options['page_title_val']);
    }

    return $title;
}

/**
 * 2. MASTER HEADER INJECTION ENGINE
 */
add_action('wp_head', 'velvet_seo_inject_frontend_tags', 1);
function velvet_seo_inject_frontend_tags() {
    if (is_admin()) return;

    $options = velvet_seo_get_settings();
    $post_id = is_singular() ? get_the_ID() : null;

    echo "\n<!-- Velvet SEO Tags -->\n";

    // A. GOOGLE SITE VERIFICATION
    if (isset($options['google_verification_enable']) && $options['google_verification_enable'] === 'yes') {
        if (!empty($options['google_verification_tag'])) {
            echo '<meta name="google-site-verification" content="' . esc_attr($options['google_verification_tag']) . '" />' . "\n";
        }
    }

    // B. META DESCRIPTION (Specific -> Global -> Excerpt)
    $meta_desc = $post_id ? get_post_meta($post_id, '_velvet_post_desc', true) : '';
    if (empty($meta_desc) && isset($options['meta_desc_enable']) && $options['meta_desc_enable'] === 'yes') {
        $meta_desc = $options['meta_desc_val'];
    }
    // Final fallback to Post Excerpt for singular pages
    if (empty($meta_desc) && $post_id) {
        $meta_desc = get_the_excerpt($post_id);
    }
    if (!empty($meta_desc)) {
        echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($meta_desc)) . '" />' . "\n";
    }

    // C. META KEYWORDS
    $meta_key = $post_id ? get_post_meta($post_id, '_velvet_post_keywords', true) : '';
    if (empty($meta_key) && isset($options['meta_key_enable']) && $options['meta_key_enable'] === 'yes') {
        $meta_key = $options['meta_key_val'];
    }
    if (!empty($meta_key)) echo '<meta name="keywords" content="' . esc_attr($meta_key) . '" />' . "\n";

    // D. CANONICAL LINK
    $canonical = $post_id ? get_post_meta($post_id, '_velvet_post_canonical', true) : '';
    if (empty($canonical) && isset($options['canonical_enable']) && $options['canonical_enable'] === 'yes') {
        $canonical = $options['canonical_val'];
    }
    if (empty($canonical) && $post_id) $canonical = get_permalink($post_id);
    if (!empty($canonical)) echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . "\n";

    // E. ROBOTS PROFILE
    $robots = $post_id ? get_post_meta($post_id, '_velvet_post_robots', true) : 'default';
    if (($robots === 'default' || empty($robots)) && isset($options['meta_robots_enable']) && $options['meta_robots_enable'] === 'yes') {
        $robots = $options['meta_robots_val'];
    }
    if (!empty($robots) && $robots !== 'default') echo '<meta name="robots" content="' . esc_attr($robots) . '" />' . "\n";

    // F. OPEN GRAPH (FACEBOOK)
    if (isset($options['og_enable']) && $options['og_enable'] === 'yes') {
        $og_title = $post_id ? get_post_meta($post_id, '_velvet_post_og_title', true) : '';
        if(empty($og_title)) $og_title = ($post_id) ? get_the_title($post_id) : ($options['og_title'] ?? '');

        $og_img = $post_id ? get_post_meta($post_id, '_velvet_post_og_img', true) : '';
        if(empty($og_img)) $og_img = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'full') : ($options['og_image'] ?? '');

        echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
        if (!empty($og_img)) echo '<meta property="og:image" content="' . esc_url($og_img) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url(is_singular() ? get_permalink($post_id) : home_url()) . '" />' . "\n";
    }

    // G. TWITTER CARDS
    if (isset($options['twitter_enable']) && $options['twitter_enable'] === 'yes') {
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        $tw_title = $post_id ? get_post_meta($post_id, '_velvet_post_tw_title', true) : '';
        if(empty($tw_title)) $tw_title = ($post_id) ? get_the_title($post_id) : ($options['twitter_title'] ?? '');

        echo '<meta name="twitter:title" content="' . esc_attr($tw_title) . '" />' . "\n";
    }

    // H. SCHEMA MARKUP
    $schema_json = $post_id ? get_post_meta($post_id, '_velvet_post_schema', true) : '';
    if (empty($schema_json) && isset($options['schema_enable']) && $options['schema_enable'] === 'yes') {
        $schema_json = $options['schema_json'] ?? '';
    }
    if (!empty($schema_json)) {
        echo '<script type="application/ld+json">' . $schema_json . "</script>\n";
    }

    echo "<!-- / Velvet SEO Tags -->\n\n";
}

/**
 * 3. GOOGLE TAG MANAGER (HEAD)
 */
add_action('wp_head', 'velvet_seo_inject_gtm_head', 0);
function velvet_seo_inject_gtm_head() {
    if (is_admin()) return;
    $options = velvet_seo_get_settings();
    $gtm_id = (is_singular()) ? get_post_meta(get_the_ID(), '_velvet_post_gtm_id', true) : '';
    
    if (empty($gtm_id) && isset($options['gtm_enable']) && $options['gtm_enable'] === 'yes') {
        $gtm_id = $options['gtm_id'];
    }

    if (!empty($gtm_id)) {
        $gtm_id = esc_attr(trim(strtoupper($gtm_id)));
        ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $gtm_id; ?>');</script>
<?php
    }
}

/**
 * 4. GOOGLE TAG MANAGER (BODY NOSCRIPT)
 */
add_action('wp_body_open', 'velvet_seo_inject_gtm_body');
function velvet_seo_inject_gtm_body() {
    if (is_admin()) return;
    $options = velvet_seo_get_settings();
    $gtm_id = (is_singular()) ? get_post_meta(get_the_ID(), '_velvet_post_gtm_id', true) : '';
    
    if (empty($gtm_id) && isset($options['gtm_enable']) && $options['gtm_enable'] === 'yes') {
        $gtm_id = $options['gtm_id'];
    }

    if (!empty($gtm_id)) {
        $gtm_id = esc_attr(trim(strtoupper($gtm_id)));
        ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id; ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php
    }
}