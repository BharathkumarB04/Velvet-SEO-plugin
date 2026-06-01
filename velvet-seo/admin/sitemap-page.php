<?php
if (!defined('ABSPATH')) exit;

// Verify user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'velvet-seo'));
}

$message = '';

// Process Form Submissions Safely
if (isset($_POST['velvet_seo_save_sitemap_settings']) && check_admin_referer('velvet_seo_sitemap_action', 'velvet_seo_sitemap_nonce')) {
    $sitemap_enable = isset($_POST['velvet_seo_sitemap_enable']) && $_POST['velvet_seo_sitemap_enable'] === 'yes' ? 'yes' : 'no';
    $include_posts = isset($_POST['velvet_seo_sitemap_posts']) && $_POST['velvet_seo_sitemap_posts'] === 'yes' ? 'yes' : 'no';
    $include_pages = isset($_POST['velvet_seo_sitemap_pages']) && $_POST['velvet_seo_sitemap_pages'] === 'yes' ? 'yes' : 'no';

    update_option('velvet_seo_sitemap_enable', $sitemap_enable);
    update_option('velvet_seo_sitemap_include_posts', $include_posts);
    update_option('velvet_seo_sitemap_include_pages', $include_pages);

    $message = 'saved';
}

$sitemap_enable = get_option('velvet_seo_sitemap_enable', 'yes');
$include_posts  = get_option('velvet_seo_sitemap_include_posts', 'yes');
$include_pages  = get_option('velvet_seo_sitemap_include_pages', 'yes');
?>

<div class="wrap velvet-seo-wrapper">
    <div class="velvet-seo-header">
        <h1>XML Sitemap Manager <span class="v-badge">v1.0</span></h1>
        <p class="subtitle">Generate structured index blueprints automatically to accelerate node processing in Google Search Console.</p>
    </div>

    <?php if ($message === 'saved') : ?>
        <div class="velvet-notice success"><p><strong>Sitemap configurations deployed!</strong> Your structured index configurations are active.</p></div>
    <?php endif; ?>

    <form method="post" action="" id="velvet-seo-sitemap-form">
        <?php wp_nonce_field('velvet_seo_sitemap_action', 'velvet_seo_sitemap_nonce'); ?>

        <div class="velvet-grid">
            
            <div class="velvet-card">
                <div class="card-header">
                    <h3>XML Sitemap Indexing</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo_sitemap_enable" value="yes" <?php checked($sitemap_enable, 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo_sitemap_enable" value="no" <?php checked($sitemap_enable, 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body">
                    <label class="field-title">Live Sitemap Reference Resource</label>
                    <p style="margin: 5px 0 15px 0;">Your live blueprint address: 
                        <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">
                            <?php echo esc_url(home_url('/sitemap.xml')); ?>
                        </a>
                    </p>
                </div>
            </div>

            <div class="velvet-card">
                <div class="card-header">
                    <h3>Content Strategy Mapping</h3>
                </div>
                <div class="card-body">
                    <div class="field-group" style="margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500;">
                            <input type="checkbox" name="velvet_seo_sitemap_posts" value="yes" <?php checked($include_posts, 'yes'); ?>>
                            Include Standard Blog Posts (<code>post</code>)
                        </label>
                    </div>
                    <div class="field-group">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500;">
                            <input type="checkbox" name="velvet_seo_sitemap_pages" value="yes" <?php checked($include_pages, 'yes'); ?>>
                            Include Static Layout Pages (<code>page</code>)
                        </label>
                    </div>
                </div>
            </div>

        </div>

        <div class="velvet-action-bar">
            <input type="submit" name="velvet_seo_save_sitemap_settings" class="button button-primary v-btn-save" value="Save Sitemap Settings">
        </div>
    </form>
</div>