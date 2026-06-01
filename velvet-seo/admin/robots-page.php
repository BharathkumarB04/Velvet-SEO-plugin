<?php
if (!defined('ABSPATH')) exit;

// Verify user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'velvet-seo'));
}

$message = '';

// Process Form Submissions Safely
if (isset($_POST['velvet_seo_save_robots']) && check_admin_referer('velvet_seo_robots_action', 'velvet_seo_robots_nonce')) {
    
    // 1. Capture Toggle State Context Rules
    $robots_enable = isset($_POST['velvet_seo_robots_enable']) && $_POST['velvet_seo_robots_enable'] === 'yes' ? 'yes' : 'no';
    update_option('velvet_seo_robots_txt_enable', $robots_enable);

    // 2. Capture and Clean Text Directives matrix streams safely
    $robots_content = isset($_POST['velvet_seo_robots_txt']) ? sanitize_textarea_field(wp_unslash($_POST['velvet_seo_robots_txt'])) : '';
    update_option('velvet_seo_robots_txt_content', $robots_content);
    
    $message = 'saved';
}

// Fetch states from storage layers safely
$is_enabled     = get_option('velvet_seo_robots_txt_enable', 'no');
$current_robots = get_option('velvet_seo_robots_txt_content', "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php");
?>

<div class="wrap velvet-seo-wrapper">
    <div class="velvet-seo-header">
        <h1>Robots.txt Manager <span class="v-badge">v1.0</span></h1>
        <p class="subtitle">Direct crawler engines, structure restriction loops, and control search engine visibility seamlessly.</p>
    </div>

    <?php if ($message === 'saved') : ?>
        <div class="velvet-notice success"><p><strong>Robots.txt rules updated successfully!</strong> The crawler directive matrices have been refreshed.</p></div>
    <?php endif; ?>

    <form method="post" action="" id="velvet-seo-robots-form">
        <?php wp_nonce_field('velvet_seo_robots_action', 'velvet_seo_robots_nonce'); ?>

        <div class="velvet-grid" style="grid-template-columns: 1fr;">
            
            <!-- Robots Card Controller Context Interface block -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Modify Crawler Instructions</h3>
                    
                    <!-- Velvet System Toggle Component Hook -->
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo_robots_enable" value="yes" <?php checked($is_enabled, 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo_robots_enable" value="no" <?php checked($is_enabled, 'no'); ?>> No</label>
                    </div>
                </div>
                
                <div class="card-body toggle-content">
                    <label class="field-title">Virtual Robots.txt Content</label>
                    <textarea name="velvet_seo_robots_txt" class="large-text raw-code" rows="10" placeholder="User-agent: *..." style="font-family: monospace; font-size: 13px; line-height: 1.5; padding: 12px; background: #f8fafc; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;"><?php echo esc_textarea($current_robots); ?></textarea>
                    <p class="description" style="font-size: 11px; color: #64748b; margin-top: 8px;">
                        Make sure you don't accidentally block your whole site unless intended. The virtual file will dynamically intercept queries hitting <code>/robots.txt</code>.
                    </p>
                </div>
            </div>

        </div>

        <div class="velvet-action-bar">
            <input type="submit" name="velvet_seo_save_robots" class="button button-primary v-btn-save" value="Save Robots Directives">
        </div>
    </form>
</div>