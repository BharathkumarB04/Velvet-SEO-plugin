# Velvet SEO v1.0

Velvet SEO is a modern, lightweight, and professional WordPress SEO plugin built for high-performance metadata management, structured schema deployments, tracking integrations, and virtual crawlers routing. Developed clean-slate in PHP without bloat, it balances granular post-level configurations with sweeping global optimization profiles.

---

## 🚀 Key Features

* **Advanced Meta Tag Management** – Complete programmatic control over browser custom titles, absolute canonical pathways, structural meta-keywords, and author signatures.
* **Social Graphs Layout Engine** – Native mapping for Facebook Open Graph (`og:`) arrays and X/Twitter Open Cards context profiles with fallback features for Featured Images.
* **Google Search Console Verification** – Fast-pass naked token verification pipeline built straight into the header array.
* **Google Tag Manager (GTM) Integration** – Synchronized payload balancing injecting container scripts into both `wp_head` (priority async) and fallback tracking scripts into `wp_body_open`.
* **Virtual Asset Router** – Intercepts engine requests dynamically to serve virtual configuration files directly from the site root without touching the physical file system.
* **Enterprise-Grade Security** – Enforced with WP Nonce security checking, explicit admin capabilities parsing (`manage_options`), data context unslashing, and sanitization loops (`sanitize_text_field`, `esc_url_raw`, `wp_kses`).

---

## 🌐 Virtual File Paths Mapping

Velvet SEO does not generate physical text or XML files on your server disk space. Instead, it hooks directly into the WordPress routing execution stack to catch requests at the root level.

| Asset Type | Global Production URL | Local Staging Reference (XAMPP example) | Behind-the-Scenes Engine Hook |
| :--- | :--- | :--- | :--- |
| **Robots Directives** | `https://example.com/robots.txt` | `http://localhost/samplewp/robots.txt` | `add_filter('robots_txt', ...)` |
| **XML Sitemap** | `https://example.com/sitemap.xml` | `http://localhost/samplewp/sitemap.xml` | Custom Endpoint via `$wp_rewrite` |

> ⚠️ **Development Note:** For virtual file interceptors to function, ensure there are no physical files named `robots.txt` or `sitemap.xml` inside your WordPress root folder. Physical assets take server priority and will completely bypass the plugin code. "Pretty Permalinks" (e.g., Post Name) must also be enabled under WordPress Settings.

---

## 📁 File Structure Matrix

```text
velvet-seo/
├── velvet-seo.php             # Core Bootstrapper & Activation Logic
├── admin/
│   ├── settings-page.php      # Global Control Dashboard Configuration 
│   ├── robots-page.php        # Robots.txt Override Module UI
│   ├── sitemap-page.php       # Dynamic XML Sitemap Controller UI
│   ├── admin.css              # Control Panel Layout Engine CSS
│   └── admin.js               # Toggle Animators & Color Picker Scripts
└── includes/
    ├── helpers.php            # Context Sanitisers & Database Accessors
    ├── frontend-meta.php      # Header & Body Tag Injection Hook Filters
    ├── post-seo-meta.php      # Individual Post/Page Meta Box Input UI
    └── seo-assets-generator.php # Rewrite Rules Controller & Hard-Flush Matrix

⚠️ **Development Note:** After installed and activated the plugin, Settings > Permalinks, set save changes for better performance.
