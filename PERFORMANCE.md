# Performance

Ashlar should be fast with native blocks and still fast for people who use Elementor. This file says how.

## Short answer

Yes, we can support both without slowing either, if three rules hold:

1. **Gutenberg is the base.** The theme and the plugin are built on native blocks and `theme.json`. They load nothing for Elementor.
2. **Elementor support is an opt-in add-on**, a separate plugin (`ashlar-elementor`) that does nothing unless Elementor is active, and loads its assets only on pages built with Elementor.
3. **Nothing loads on every page by default.** Each script, style and font is attached to the block, kit or page that needs it.

An honest limit: Elementor itself adds CSS, JavaScript and markup on the pages it builds, so an Elementor-built page will not be as light as the same page made of native blocks. What we control is that pages made with blocks stay light, that our Elementor add-on is small, and that the site works well with Elementor's own optimization settings.

## Structure

| Piece | Rule |
|---|---|
| Theme | Block theme. Styles from `theme.json` are generated once and are small. No jQuery, no icon fonts, no theme-wide stylesheet beyond what the blocks need. |
| `ashlar-kits` plugin | Block patterns and a kit importer. Admin code loads only in the admin. The front end gets no scripts or styles unless a kit block needs them. |
| `ashlar-elementor` add-on | Hooks in only after Elementor has loaded. Registers widgets and templates. Its files are never loaded without Elementor. |
| Kits | A kit's CSS and JavaScript load only on pages that use the kit. |

## Rules

**Assets**
- Load each script and style conditionally (by block, template or page), never site-wide.
- Scripts use `defer` through the `strategy` argument. No render-blocking scripts in the head.
- Each interactive feature is optional and opt-in. Content works without JavaScript.
- Use `wp_enqueue_block_style()` and the "separate core block assets" setting (already on) so core block CSS loads per block.
- No third-party requests on the front end by default (fonts, analytics, widgets). Self-host.

**Images and fonts**
- Every image has `width` and `height`, `loading="lazy"` below the fold and `fetchpriority="high"` for the main image above it.
- Use WebP or AVIF and responsive sizes (`srcset`).
- One font family (Inter), self-hosted as WOFF2, with `font-display: swap`. Preload only the weight used above the fold.

**Markup**
- Use native blocks and semantic HTML. Avoid deep wrapper nesting: every extra `div` costs rendering time and bytes.
- No inline CSS blocks repeated per instance.

**Database and PHP**
- Keep autoloaded options small. Store large data with `autoload = false`.
- No remote calls or heavy queries on every page view. Cache results in transients or the object cache.
- No `WP_Query` with `posts_per_page = -1` on the front end.

**Caching layers (stage and production)**
1. OPcache with `validate_timestamps = 0` (the dev stack leaves it on so edits show).
2. A full-page cache (host cache, nginx FastCGI cache, or a cache plugin) for logged-out visitors.
3. A persistent object cache (Redis or Memcached) for dynamic sites.
4. Long-lived browser caching for versioned static files, and a CDN where traffic justifies one.

## Targets

Use the Core Web Vitals "good" thresholds, measured at the 75th percentile of real visits and tested in the lab on a mid-range mobile profile:

| Metric | Good |
|---|---|
| Largest Contentful Paint (LCP) | 2.5 seconds or less |
| Interaction to Next Paint (INP) | 200 milliseconds or less |
| Cumulative Layout Shift (CLS) | 0.1 or less |

Set stricter internal budgets per kit when there are numbers to base them on. Do not publish a Lighthouse score as a promise: lab scores vary with the host.

## Measure, do not guess

- **Query Monitor** (installed in the dev stack): slow queries, hooks, scripts and styles loaded per page, HTTP calls, PHP errors.
- **Lighthouse** and **WebPageTest** on kit pages, mobile profile, before and after each change.
- **A page-weight check** per kit page: total CSS, JavaScript and images. Fail the review if a block page loads a script it does not use.
- Compare a kit page built with blocks against the same page built with Elementor, and record both numbers in `docs/`.

## Fast without Cloudflare

Cloudflare (or any CDN) should make Ashlar faster, never make it usable. Build and test the origin as if the CDN were not there, then add the CDN on top.

**What Cloudflare normally hides, and what Ashlar must do itself**

| Cloudflare does | Ashlar and the origin do instead |
|---|---|
| Caches full pages at the edge | Full-page cache at the origin for logged-out visitors (nginx FastCGI cache, the host's cache, or one cache plugin). Target a cached time to first byte (TTFB) of about 200 ms or less at the origin. |
| Caches static files at the edge | Long `Cache-Control` with `immutable` on versioned CSS, JS, fonts and images. Compressed with gzip, and Brotli where available. |
| Compresses and resizes images (paid tiers) | Images are optimized and resized at build time and on upload (WebP or AVIF, responsive sizes). Never rely on a CDN image feature. |
| Terminates TLS and speaks HTTP/2 and HTTP/3 | The origin serves HTTP/2 (HTTP/3 where the host supports it) with a modern TLS configuration. |
| Minifies or defers scripts (Rocket Loader and similar) | Our scripts are already small, deferred and loaded only where needed. Do not depend on a CDN rewriting them, because that can break scripts. |
| Absorbs traffic spikes and bots | PHP-FPM tuned for the server size, a persistent object cache, rate limiting on the login, and a page cache so most views never reach PHP. |

**Origin requirements (stage and production)**

1. PHP 8.4 or newer, the latest patch release, with OPcache on and `validate_timestamps = 0`.
2. A full-page cache that skips logged-in users, carts and form posts.
3. A persistent object cache (Redis or Memcached) when the site is dynamic.
4. Database tuned: indexes on custom queries, autoloaded options small, no per-request remote calls.
5. HTTP/2, compression, and long cache headers for versioned assets.
6. No render-blocking third-party requests.

**How to prove it**

- Test the origin directly: put Cloudflare in DNS-only mode (or call the origin by its hostname) and run Lighthouse and WebPageTest on key pages. Record TTFB, LCP, INP and CLS with and without the CDN.
- If the origin numbers are poor without the CDN, fix the origin. A fast cache hit at the edge can hide a slow site, and every cache miss and every logged-in visit still reaches the origin.
- Repeat after each release. Add the result to `docs/`.

**What Cloudflare features not to rely on**

Do not build the site so that it needs Rocket Loader, Auto Minify-style rewriting, Mirage, Polish or similar. They change markup or scripts after the fact, and they can break accessibility or scripts. If you turn any on, test the result, including keyboard use and screen readers.

## SEO, answer engines and accessibility

These are properties of the markup, so they do not depend on caching. They do depend on speed to some extent (Core Web Vitals are a ranking signal, and slow pages are crawled less).

- **Rendered HTML.** Content is in the server-rendered HTML, not injected by JavaScript. Crawlers and AI answer engines read it with no script running.
- **Structure.** One H1 per page, headings in order, landmarks, descriptive link text, labelled forms. The kits are audited for this (the Universal Template audit applies to the patterns).
- **Facts in the right elements.** Addresses in `address`, phone numbers as `tel:` links, dates in `time`, specs in tables, questions and answers as visible text, breadcrumbs as `nav` plus an ordered list.
- **Structured data.** Honest JSON-LD for the page type (Organization, WebSite, Article, FAQPage, LocalBusiness, BreadcrumbList), generated server-side by the plugin. Never mark up content that is not on the page.
- **Meta.** Unique title and description, canonical URL, Open Graph tags, a sitemap and `robots.txt`. Block indexing on local and stage.
- **Accessibility.** WCAG 2.2 AA. Contrast is checked in light and dark mode. Visible focus, keyboard operation, reduced-motion support, text alternatives for images.
- **Elementor caution.** Elementor markup can add many wrapper elements and its widgets may not output the right heading levels or landmarks. The `ashlar-elementor` add-on must output semantic markup and the same heading and landmark structure as the block kits. Test Elementor pages with the same audits.

## Elementor support plan

1. Build the Gutenberg kits first. They are the reference and the default.
2. Build the Elementor add-on in its own repository (`ashlar-wordpress-elementor`): a thin layer that exposes the same kits as Elementor widgets or templates, reusing the kit's CSS and sharing the same design tokens.
3. In the add-on, test with Elementor's own performance settings on. Document which settings to turn on. Do not claim a speed result until you have measured it.
4. Keep Elementor out of the default dev stack. Install it in a separate test site when working on the add-on.

## Things that quietly make WordPress slow

- Many plugins that each add their own scripts and styles to every page.
- A page builder's wrappers on top of blocks.
- Unoptimized images, and fonts from third parties.
- Heavy sliders and animation libraries loaded site-wide.
- No page cache, or no persistent object cache on dynamic pages.
- A cheap host with an old PHP version. Use PHP 8.4 or newer.
