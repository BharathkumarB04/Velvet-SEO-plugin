<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. REGISTER THE FULL VELVET SEO META BOX
 */
add_action('add_meta_boxes', 'velvet_seo_register_post_meta_box');
function velvet_seo_register_post_meta_box() {
    $screens = array('post', 'page');
    foreach ($screens as $screen) {
        add_meta_box(
            'velvet_seo_post_settings',
            __('Velvet SEO Page Optimizer', 'velvet-seo'),
            'velvet_seo_render_meta_box_callback',
            $screen,
            'normal',
            'high'
        );
    }
}

/**
 * 2. RENDER THE ALL-IN-ONE METABOX UI WITH GTM
 */
function velvet_seo_render_meta_box_callback($post) {
    wp_nonce_field('velvet_seo_post_meta_nonce_action', 'velvet_seo_post_meta_nonce');

    // Fetch all existing individual post meta keys
    $v_meta = array(
        'title'       => get_post_meta($post->ID, '_velvet_post_title', true),
        'desc'        => get_post_meta($post->ID, '_velvet_post_desc', true),
        'keywords'    => get_post_meta($post->ID, '_velvet_post_keywords', true),
        'canonical'   => get_post_meta($post->ID, '_velvet_post_canonical', true),
        'robots'      => get_post_meta($post->ID, '_velvet_post_robots', true),
        'theme_color' => get_post_meta($post->ID, '_velvet_post_theme_color', true),
        'author'      => get_post_meta($post->ID, '_velvet_post_author', true),
        'og_title'    => get_post_meta($post->ID, '_velvet_post_og_title', true),
        'og_desc'     => get_post_meta($post->ID, '_velvet_post_og_desc', true),
        'og_img'      => get_post_meta($post->ID, '_velvet_post_og_img', true),
        'tw_title'    => get_post_meta($post->ID, '_velvet_post_tw_title', true),
        'tw_desc'     => get_post_meta($post->ID, '_velvet_post_tw_desc', true),
        'tw_img'      => get_post_meta($post->ID, '_velvet_post_tw_img', true),
        'gtm_id'      => get_post_meta($post->ID, '_velvet_post_gtm_id', true),
        'schema'      => get_post_meta($post->ID, '_velvet_post_schema', true),
    );

    if(empty($v_meta['robots'])) $v_meta['robots'] = 'default';
    ?>
    <style>
        .v-meta-wrapper { font-family: sans-serif; padding: 10px 5px; color: #1e293b; }
        .v-meta-section-title { font-size: 14px; font-weight: 700; color: #2271b1; margin: 25px 0 12px 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 5px; }
        .v-meta-section-title:first-of-type { margin-top: 0; }
        .v-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 782px) { .v-meta-grid { grid-template-columns: 1fr; } }
        .v-meta-row { margin-bottom: 15px; }
        .v-meta-row.full-width { grid-column: span 2; }
        @media (max-width: 782px) { .v-meta-row.full-width { grid-column: span 1; } }
        .v-meta-row label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155; }
        .v-meta-row input[type="text"], .v-meta-row input[type="url"], .v-meta-row textarea, .v-meta-row select { 
            width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 13px; background: #fff;
        }
        .v-meta-row textarea.monospace { font-family: monospace; font-size: 12px; background: #f8fafc; }
        .v-char-hint { font-size: 11px; color: #94a3b8; text-align: right; margin-top: 3px; }
        .v-field-note { font-size: 11px; color: #64748b; font-style: italic; margin-top: 2px; }
    </style>

    <div class="v-meta-wrapper">
        
        <div class="v-meta-section-title">1. Core Metadata Engine</div>
        <div class="v-meta-grid">
            <div class="v-meta-row">
                <label>SEO Title Override</label>
                <input type="text" id="v_p_title" name="v_p_title" value="<?php echo esc_attr($v_meta['title']); ?>" placeholder="Defaults to global or native theme post title" maxlength="60">
                <div class="v-char-hint"><span id="v_p_title_cnt">0</span>/60 chars</div>
            </div>
            <div class="v-meta-row">
                <label>Canonical URL Override</label>
                <input type="url" name="v_p_canonical" value="<?php echo esc_url($v_meta['canonical']); ?>" placeholder="https://example.com/this-page-url/">
            </div>
            <div class="v-meta-row full-width">
                <label>Meta Description Snippet</label>
                <textarea id="v_p_desc" name="v_p_desc" rows="3" placeholder="Write a custom description extract for search result clips..." maxlength="160"><?php echo esc_textarea($v_meta['desc']); ?></textarea>
                <div class="v-char-hint"><span id="v_p_desc_cnt">0</span>/160 chars</div>
            </div>
            <div class="v-meta-row">
                <label>Meta Keywords</label>
                <input type="text" name="v_p_keywords" value="<?php echo esc_attr($v_meta['keywords']); ?>" placeholder="page, tags, custom, keywords">
            </div>
            <div class="v-meta-row">
                <label>Page Robots Directive</label>
                <select name="v_p_robots">
                    <option value="default" <?php selected($v_meta['robots'], 'default'); ?>>Follow Global Config Setup</option>
                    <option value="index, follow" <?php selected($v_meta['robots'], 'index, follow'); ?>>index, follow</option>
                    <option value="noindex, follow" <?php selected($v_meta['robots'], 'noindex, follow'); ?>>noindex, follow</option>
                    <option value="noindex, nofollow" <?php selected($v_meta['robots'], 'noindex, nofollow'); ?>>noindex, nofollow</option>
                </select>
            </div>
            <div class="v-meta-row">
                <label>Browser Canvas Theme Color (Hex)</label>
                <input type="text" name="v_p_theme_color" value="<?php echo esc_attr($v_meta['theme_color']); ?>" placeholder="#2271b1">
            </div>
            <div class="v-meta-row">
                <label>Custom Post Author Name</label>
                <input type="text" name="v_p_author" value="<?php echo esc_attr($v_meta['author']); ?>" placeholder="Override dynamic author attribution">
            </div>
        </div>

        <div class="v-meta-section-title">2. Facebook Open Graph Setup</div>
        <div class="v-meta-grid">
            <div class="v-meta-row">
                <label>og:title Override</label>
                <input type="text" name="v_p_og_title" value="<?php echo esc_attr($v_meta['og_title']); ?>" placeholder="Fallback to custom page title">
            </div>
            <div class="v-meta-row">
                <label>og:image Asset Target Link</label>
                <input type="url" name="v_p_og_img" value="<?php echo esc_url($v_meta['og_img']); ?>" placeholder="https://example.com/social-card.jpg">
            </div>
            <div class="v-meta-row full-width">
                <label>og:description Summary</label>
                <textarea name="v_p_og_desc" rows="2" placeholder="Fallback to custom description tag context"><?php echo esc_textarea($v_meta['og_desc']); ?></textarea>
            </div>
        </div>

        <div class="v-meta-section-title">3. X / Twitter Card Interface</div>
        <div class="v-meta-grid">
            <div class="v-meta-row">
                <label>twitter:title</label>
                <input type="text" name="v_p_tw_title" value="<?php echo esc_attr($v_meta['tw_title']); ?>" placeholder="Fallback to social facebook title card">
            </div>
            <div class="v-meta-row">
                <label>twitter:image URL</label>
                <input type="url" name="v_p_tw_img" value="<?php echo esc_url($v_meta['tw_img']); ?>" placeholder="https://example.com/twitter-card.jpg">
            </div>
            <div class="v-meta-row full-width">
                <label>twitter:description Summary</label>
                <textarea name="v_p_tw_desc" rows="2" placeholder="Fallback to social facebook description text"><?php echo esc_textarea($v_meta['tw_desc']); ?></textarea>
            </div>
        </div>

        <div class="v-meta-section-title">4. Google Tag Manager Container Override</div>
        <div class="v-meta-grid">
            <div class="v-meta-row">
                <label>GTM Container ID Override</label>
                <input type="text" name="v_p_gtm_id" value="<?php echo esc_attr($v_meta['gtm_id']); ?>" placeholder="e.g., GTM-NK83JSW" style="text-transform: uppercase;">
                <div class="v-field-note">Leave blank to inherit the global GTM fallback token tracking script configured in core settings.</div>
            </div>
        </div>

        <div class="v-meta-section-title">5. Structured Rich Schema Markup Injection</div>
        <div class="v-meta-row full-width">
            <label>JSON-LD Data String Payload Block</label>
            <textarea name="v_p_schema" class="monospace" rows="5" placeholder='{ "@context": "https://schema.org", "@type": "Article" }'><?php echo esc_textarea($v_meta['schema']); ?></textarea>
            <div class="v-field-note">Page specific payload handles rich schema logic cleanly. Raw script code wrapper tags are dropped.</div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function initMetaCounters(inputId, displayId) {
                const element = document.getElementById(inputId);
                const screen = document.getElementById(displayId);
                if(!element || !screen) return;
                const calc = () => screen.textContent = element.value.length;
                element.addEventListener('input', calc);
                calc();
            }
            initMetaCounters('v_p_title', 'v_p_title_cnt');
            initMetaCounters('v_p_desc', 'v_p_desc_cnt');
        });
    </script>
    <?php
}

/**
 * 3. CORE SAVE POST METADATA PROCESSOR
 */
add_action('save_post', 'velvet_seo_save_post_meta_data');
function velvet_seo_save_post_meta_data($post_id) {
    if (!isset($_POST['velvet_seo_post_meta_nonce']) || !wp_verify_nonce($_POST['velvet_seo_post_meta_nonce'], 'velvet_seo_post_meta_nonce_action')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = array(
        'v_p_title'       => '_velvet_post_title',
        'v_p_desc'        => '_velvet_post_desc',
        'v_p_keywords'    => '_velvet_post_keywords',
        'v_p_canonical'   => '_velvet_post_canonical',
        'v_p_robots'      => '_velvet_post_robots',
        'v_p_theme_color' => '_velvet_post_theme_color',
        'v_p_author'      => '_velvet_post_author',
        'v_p_og_title'    => '_velvet_post_og_title',
        'v_p_og_desc'     => '_velvet_post_og_desc',
        'v_p_og_img'      => '_velvet_post_og_img',
        'v_p_tw_title'    => '_velvet_post_tw_title',
        'v_p_tw_desc'     => '_velvet_post_tw_desc',
        'v_p_tw_img'      => '_velvet_post_tw_img',
        'v_p_gtm_id'      => '_velvet_post_gtm_id',
        'v_p_schema'      => '_velvet_post_schema'
    );

    foreach ($fields as $post_key => $db_meta_key) {
        if (isset($_POST[$post_key])) {
            $val = $_POST[$post_key];
            if ($post_key === 'v_p_schema') {
                update_post_meta($post_id, $db_meta_key, trim($val));
            } elseif (strpos($post_key, 'img') !== false || strpos($post_key, 'canonical') !== false) {
                update_post_meta($post_id, $db_meta_key, esc_url_raw(trim($val)));
            } else {
                update_post_meta($post_id, $db_meta_key, sanitize_text_field(trim($val)));
            }
        }
    }
}