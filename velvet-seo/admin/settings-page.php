<?php
if (!defined('ABSPATH')) exit;

// Verify user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'velvet-seo'));
}

// Process Form Submissions Safely
$message = '';
if (isset($_POST['velvet_seo_save_settings']) && check_admin_referer('velvet_seo_action_nonce', 'velvet_seo_nonce')) {
    $input = isset($_POST['velvet_seo']) ? $_POST['velvet_seo'] : array();
    $sanitized = array();

    // Pull previously saved settings configurations to preserve values dropped by JS toggles
    $old_options = get_option('velvet_seo_settings', array());

    // Loop fields and clean values conditionally
    $defaults = velvet_seo_get_defaults();
    foreach ($defaults as $key => $default_val) {
        
        // FIX: Check if the key is hidden or wasn't sent via the POST payload by the browser
        if (!isset($input[$key])) {
            // Keep the previous DB value if available; otherwise, drop back to the default profile configuration
            $sanitized[$key] = isset($old_options[$key]) ? $old_options[$key] : $default_val;
            continue;
        }

        if (strpos($key, '_enable') !== false) {
            $sanitized[$key] = ($input[$key] === 'yes') ? 'yes' : 'no';
        } elseif ($key === 'schema_json') {
            // FIX: Run wp_unslash first to kill the backslashes \" issue completely
            $unslashed_json = wp_unslash($input[$key]);
            $sanitized[$key] = trim($unslashed_json);
        } elseif (strpos($key, 'url') !== false || strpos($key, 'image') !== false) {
            $sanitized[$key] = esc_url_raw(trim($input[$key]));
        } elseif ($key === 'google_verification_tag') {
            // FIX: Allow standard meta tag formats or raw verification hashes safely
            $sanitized[$key] = sanitize_text_field(wp_unslash($input[$key]));
        } else {
            // Run wp_unslash here too just in case standard values carry escaping indicators
            $sanitized[$key] = sanitize_text_field(wp_unslash($input[$key]));
        }
    }

    update_option('velvet_seo_settings', $sanitized);
    $message = 'saved';
}

// Reset Action handling
if (isset($_POST['velvet_seo_reset_settings']) && check_admin_referer('velvet_seo_action_nonce', 'velvet_seo_nonce')) {
    update_option('velvet_seo_settings', velvet_seo_get_defaults());
    $message = 'reset';
}

$options = velvet_seo_get_settings();
?>

<div class="wrap velvet-seo-wrapper">
    <div class="velvet-seo-header">
        <h1>Velvet SEO <span class="v-badge">v1.0</span></h1>
        <p class="subtitle">Configure production-grade metadata, social cards, and crawl rules globally seamlessly.</p>
    </div>

    <?php if ($message === 'saved') : ?>
        <div class="velvet-notice success"><p><strong>SEO Settings saved successfully!</strong></p></div>
    <?php elseif ($message === 'reset') : ?>
        <div class="velvet-notice error"><p><strong>All configuration structures reset to system defaults.</strong></p></div>
    <?php endif; ?>

    <form method="post" action="" id="velvet-seo-form">
        <?php wp_nonce_field('velvet_seo_action_nonce', 'velvet_seo_nonce'); ?>

        <div class="velvet-grid">
            <!-- CARD 1: Page Title -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Title Override</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[page_title_enable]" value="yes" <?php checked($options['page_title_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[page_title_enable]" value="no" <?php checked($options['page_title_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Global Browser Window Title Override</label>
                    <input type="text" name="velvet_seo[page_title_val]" value="<?php echo esc_attr($options['page_title_val']); ?>" class="large-text count-target" maxlength="60">
                    <div class="counter-info"><span class="char-count">0</span>/60 characters (Optimal)</div>
                </div>
            </div>

            <!-- CARD 2: Meta Description -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Meta Description</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[meta_desc_enable]" value="yes" <?php checked($options['meta_desc_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[meta_desc_enable]" value="no" <?php checked($options['meta_desc_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Global Snippet Text Description</label>
                    <textarea name="velvet_seo[meta_desc_val]" class="large-text count-target" rows="3" maxlength="160"><?php echo esc_textarea($options['meta_desc_val']); ?></textarea>
                    <div class="counter-info"><span class="char-count">0</span>/160 characters (Optimal)</div>
                </div>
            </div>

            <!-- CARD 3: Meta Keywords -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Meta Keywords</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[meta_key_enable]" value="yes" <?php checked($options['meta_key_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[meta_key_enable]" value="no" <?php checked($options['meta_key_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Target Keyphrases (Comma separated)</label>
                    <textarea name="velvet_seo[meta_key_val]" class="large-text" rows="2" placeholder="seo, wordpress plugin, velvet optimization"><?php echo esc_textarea($options['meta_key_val']); ?></textarea>
                </div>
            </div>

            <!-- CARD 4: Canonical Configuration -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Canonical Link Indexing</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[canonical_enable]" value="yes" <?php checked($options['canonical_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[canonical_enable]" value="no" <?php checked($options['canonical_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Canonical Target Absolute URL</label>
                    <input type="url" name="velvet_seo[canonical_val]" value="<?php echo esc_url($options['canonical_val']); ?>" class="large-text" placeholder="https://example.com/">
                </div>
            </div>

            <!-- CARD 5: Meta Robots Dropdown Selector -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Meta Robots Core Configuration</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[meta_robots_enable]" value="yes" <?php checked($options['meta_robots_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[meta_robots_enable]" value="no" <?php checked($options['meta_robots_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Robots Crawler Header Directives</label>
                    <select name="velvet_seo[meta_robots_val]" class="velvet-select">
                        <option value="index, follow" <?php selected($options['meta_robots_val'], 'index, follow'); ?>>index, follow (Standard Default)</option>
                        <option value="noindex, follow" <?php selected($options['meta_robots_val'], 'noindex, follow'); ?>>noindex, follow (Hide Page, Follow Links)</option>
                        <option value="index, nofollow" <?php selected($options['meta_robots_val'], 'index, nofollow'); ?>>index, nofollow (Index Page, Ignore Links)</option>
                        <option value="noindex, nofollow" <?php selected($options['meta_robots_val'], 'noindex, nofollow'); ?>>noindex, nofollow (Total Restrict Profile)</option>
                    </select>
                </div>
            </div>

            <!-- CARD 6: System Color theme UI -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Browser Canvas Theme Color</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[theme_color_enable]" value="yes" <?php checked($options['theme_color_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[theme_color_enable]" value="no" <?php checked($options['theme_color_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Hex Interface Mobile Bar Color Picker</label><br>
                    <input type="text" name="velvet_seo[theme_color_val]" value="<?php echo esc_attr($options['theme_color_val']); ?>" class="velvet-color-picker">
                </div>
            </div>

            <!-- CARD 7: Custom Author Profile -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Global Metadata Author Attribute</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[author_enable]" value="yes" <?php checked($options['author_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[author_enable]" value="no" <?php checked($options['author_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Publisher Signature / Full Author String Name</label>
                    <input type="text" name="velvet_seo[author_val]" value="<?php echo esc_attr($options['author_val']); ?>" class="large-text">
                </div>
            </div>

            <!-- CARD 8: Open Graph Data Layer Mapping -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Facebook Open Graph Layout Array</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[og_enable]" value="yes" <?php checked($options['og_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[og_enable]" value="no" <?php checked($options['og_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <div class="field-group">
                        <label>og:title</label>
                        <input type="text" name="velvet_seo[og_title]" value="<?php echo esc_attr($options['og_title']); ?>" class="large-text">
                    </div>
                    <div class="field-group" style="margin-top:10px;">
                        <label>og:description</label>
                        <textarea name="velvet_seo[og_description]" class="large-text" rows="2"><?php echo esc_textarea($options['og_description']); ?></textarea>
                    </div>
                    <div class="field-group" style="margin-top:10px;">
                        <label>og:image Asset Link</label>
                        <input type="url" name="velvet_seo[og_image]" value="<?php echo esc_url($options['og_image']); ?>" class="large-text" placeholder="https://">
                    </div>
                    <div class="field-group" style="margin-top:10px;">
                        <label>og:url Target Absolute Reference</label>
                        <input type="url" name="velvet_seo[og_url]" value="<?php echo esc_url($options['og_url']); ?>" class="large-text" placeholder="https://">
                    </div>
                </div>
            </div>

            <!-- CARD 9: Twitter Cards Context Mapping -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>X / Twitter Open Cards Layout Engine</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[twitter_enable]" value="yes" <?php checked($options['twitter_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[twitter_enable]" value="no" <?php checked($options['twitter_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <div class="field-group">
                        <label>twitter:title</label>
                        <input type="text" name="velvet_seo[twitter_title]" value="<?php echo esc_attr($options['twitter_title']); ?>" class="large-text">
                    </div>
                    <div class="field-group" style="margin-top:10px;">
                        <label>twitter:description</label>
                        <textarea name="velvet_seo[twitter_description]" class="large-text" rows="2"><?php echo esc_textarea($options['twitter_description']); ?></textarea>
                    </div>
                    <div class="field-group" style="margin-top:10px;">
                        <label>twitter:image Card Resource Asset Link</label>
                        <input type="url" name="velvet_seo[twitter_image]" value="<?php echo esc_url($options['twitter_image']); ?>" class="large-text" placeholder="https://">
                    </div>
                </div>
            </div>

            <!-- CARD 10: Rich Schema Injection Engine -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>JSON-LD Structured Rich Data Engine</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[schema_enable]" value="yes" <?php checked($options['schema_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[schema_enable]" value="no" <?php checked($options['schema_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Structured Payload block (Raw &lt;script&gt; wrapper tags are excluded automatically)</label>
                    <textarea name="velvet_seo[schema_json]" class="large-text raw-code" rows="6" placeholder='{ "@context": "https://schema.org", "@type": "Organization" }'><?php echo esc_textarea($options['schema_json']); ?></textarea>
                </div>
            </div>

            <!-- CARD 11: Google Tag Manager -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Google Tag Manager (GTM)</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[gtm_enable]" value="yes" <?php checked($options['gtm_enable'], 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[gtm_enable]" value="no" <?php checked($options['gtm_enable'], 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Enter your GTM Container ID</label>
                    <input type="text" name="velvet_seo[gtm_id]" value="<?php echo esc_attr($options['gtm_id']); ?>" class="large-text" placeholder="e.g., GTM-NK83JSW" style="text-transform: uppercase;">
                    <p class="description" style="font-size: 11px; color: #64748b; margin-top: 5px;">Just paste your unique ID. The engine automatically handles container integration scripts for both head and body loops.</p>
                </div>
            </div>

            <!-- CARD 12: Google Site Verification -->
            <div class="velvet-card" data-toggle-card>
                <div class="card-header">
                    <h3>Google Site Verification</h3>
                    <div class="switch-toggle">
                        <label><input type="radio" name="velvet_seo[google_verification_enable]" value="yes" <?php checked(isset($options['google_verification_enable']) ? $options['google_verification_enable'] : 'no', 'yes'); ?>> Yes</label>
                        <label><input type="radio" name="velvet_seo[google_verification_enable]" value="no" <?php checked(isset($options['google_verification_enable']) ? $options['google_verification_enable'] : 'no', 'no'); ?>> No</label>
                    </div>
                </div>
                <div class="card-body toggle-content">
                    <label class="field-title">Google Verification Token ID String</label>
                    <input type="text" name="velvet_seo[google_verification_tag]" value="<?php echo esc_attr(isset($options['google_verification_tag']) ? $options['google_verification_tag'] : ''); ?>" class="large-text" placeholder="e.g., 4zR_x8K23_ExampleTokenString-12345">
                    <p class="description" style="font-size: 11px; color: #64748b; margin-top: 5px;">Do not paste the entire HTML tag. Enter <strong>only</strong> the unique string value located inside the content="" attribute of your Search Console tag.</p>
                </div>
            </div>

        </div>

        <!-- Submission Execution Terminal Row Layout -->
        <div class="velvet-action-bar">
            <input type="submit" name="velvet_seo_save_settings" class="button button-primary v-btn-save" value="Save SEO Changes">
            <input type="submit" name="velvet_seo_reset_settings" class="button button-secondary v-btn-reset" value="Reset Configurations" onclick="return confirm('Are you sure you want to completely clear out and drop all current settings profiles?');">
        </div>
    </form>
</div>