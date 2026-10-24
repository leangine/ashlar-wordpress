# Security

Rules for writing, reviewing and releasing the Ashlar theme and plugin. Written 25 October 2026 against the situation described below. Review it at least once a month.

## What is happening now (verified 25 October 2026)

- **WordPress 7.1.3** (6 October 2026) fixed seven security issues: a stored XSS on the Comments screen, a denial of service in `WP_Http::make_absolute_url()`, a second-order SQL injection in the export tool, author-role users making posts sticky, comments on private posts readable without login, an Imgur embed XSS, and forgeable `{status}_{type}` hook parameters. Fixes were backported to older branches. Source: the WordPress.org release post and Patchstack's write-up.
- **PHP 8.5.12, 8.4.27, 8.3.36 and 8.2.35** fix two critical flaws that can allow unauthenticated remote code execution (CVE-2026-109618, CVE-2026-109619). The advisory names `mod_php` (Apache) as the highest risk. Earlier 2026 releases fixed an SQL injection in the PostgreSQL extension, an out-of-bounds write in BCMath, and a SOAP extension flaw.
- **Plugins are the main risk.** Patchstack's 2026 report counts 11,334 vulnerabilities disclosed in 2025, about 91 percent in plugins and only six in core. Cross-site scripting and broken access control are the most common types. Percentages differ between reports, so do not quote them as exact. Mass exploitation can start within hours of disclosure.

What this means for Ashlar:

1. Run the newest patch release of PHP (the dev stack checks for 8.5.12 or newer) and the newest WordPress.
2. Prefer PHP-FPM behind nginx to `mod_php`.
3. Keep our plugin small and dependency-free, and write it as if every input is hostile. XSS and access control are exactly where plugins fail.
4. Do not depend on third-party plugins for anything a block pattern or core feature can do.

## Code rules (the code review checklist)

**Output.** Escape late, at the point of output: `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()`, `wp_kses_post()` or `wp_kses()` with an explicit allowed list. This includes block attributes in render callbacks, post meta, options and anything from the database. Never echo raw `$_GET`, `$_POST` or `$_SERVER` values.

**Input.** Unslash, then validate, then sanitize: `wp_unslash()`, `absint()`, `sanitize_text_field()`, `sanitize_key()`, `sanitize_email()`, `esc_url_raw()`. Validate against an allowed list when the values are known.

**Forms and state changes.** Every action that changes data has both a nonce check (`check_admin_referer()`, `wp_verify_nonce()`, `check_ajax_referer()`) and a capability check (`current_user_can()`). A nonce is not authorization.

**REST and AJAX.** Every REST route has a real `permission_callback`. Never `__return_true` on a route that reads private data or changes anything. Every AJAX action that changes data checks the nonce and capability. `wp_ajax_nopriv_*` is for public actions only.

**Database.** Use `$wpdb->prepare()` for any query with a variable. Use `$wpdb->insert()`, `update()` and `delete()` where possible. Never concatenate user input into SQL. Use `esc_like()` for LIKE queries.

**Files.** Never include or open a path built from user input. Validate uploads with `wp_check_filetype_and_ext()`, an allow list of types and a size limit. Never accept PHP, PHAR or executable types. Store uploads in the uploads directory, and rely on the web server rule that blocks PHP execution there.

**Serialization.** Never `unserialize()` data that a user could influence. Use JSON.

**Dangerous functions.** No `eval()`, `assert()` with strings, `create_function()`, `extract()` on input, `preg_replace` with `/e`, `shell_exec` and friends, or execution of fetched or decoded code. No obfuscated code.

**Remote requests.** Use `wp_safe_remote_get()` and `wp_safe_remote_post()` so internal addresses are refused. Always set a timeout. Never disable TLS verification. Never fetch code. Do not contact external services without a clear setting and documentation. A kit importer may fetch assets only from an allowlist.

**Kit importer (highest risk feature).** Requires `manage_options` or a more specific capability, a nonce, and a validated file type and size. Imported content passes through `wp_kses_post()` unless the user has `unfiltered_html`. Never execute imported content. Never import PHP files or options that include executable code. Log failures without logging the content.

**Secrets.** No keys, tokens or passwords in the repository, ever. Read them from constants in `wp-config.php` or environment variables. Local `.env.local` values are throwaway.

**Privacy.** Do not collect or send data unless the site owner turns it on. No analytics, fonts or scripts loaded from a third party by default. Self-host assets.

**Errors and logs.** Never display errors on the page. Never log passwords, tokens or personal data.

## Dependencies and supply chain

- The theme and plugin ship with **no runtime dependencies**. Keep it that way.
- Development tools are in `composer.json`, pinned by `composer.lock`. Commit the lock file.
- `composer run check` runs coding standards (which include the WordPress security sniffs), static analysis and `composer audit` (known vulnerabilities in dependencies). It must be clean.
- Review dependency updates before merging. Do not add a package without a reason written in the pull request.
- Docker images are pinned to exact versions in the dev stack. Raise them on purpose and note why.
- Before releasing, build the package from a clean checkout, not from a working folder.

## Keeping up to date

| When | What |
|---|---|
| Every release of WordPress or PHP | Read the release notes. Security releases: update the dev stack the same day. |
| Weekly | Check core, plugin and theme updates, file integrity (`wp core verify-checksums`) and dependency advisories (`composer audit`) |
| Monthly | Review this file against current advisories. Check PHP support dates. |
| Quarterly | Review the web server and PHP hardening rules used in the maintainers' development stack. |

Sources to watch: WordPress.org news (security releases), php.net archive and security advisories, the Patchstack vulnerability database, and GitHub security advisories for our dev dependencies.

PHP support (php.net, 25 October 2026): 8.3 security fixes only until 31 December 2027, 8.4 active support until 31 December 2026 and security fixes until 31 December 2028, 8.5 active support until 31 December 2027. PHP 8.2 has reached the end of active support. Ashlar requires PHP 8.2 or newer so older hosts still work, and develops on 8.5. Revisit the minimum when 8.2 reaches end of life (end of 2026).

## Release checklist

1. `composer audit` and the coding standards are clean.
2. The maintainers' development stack passes its security checks on the latest WordPress and PHP.
3. Test as a logged-out visitor, as a subscriber and as an editor: nothing private is visible and no action is possible without the right capability.
4. Test the kit importer with a bad file (wrong type, oversized, containing script tags).
5. Review the diff for secrets, debug code, `var_dump`, `error_log` with data, and new remote calls.
6. Build the package from a clean checkout and unpack it to check its contents.
7. Tag the release on `main`.

## Reporting a problem

Please report security problems **privately**. Do not open a public issue or pull request for a vulnerability.

Use GitHub's private vulnerability reporting: open the repository's **Security** tab and choose **Report a vulnerability**, or go straight to https://github.com/leangine/ashlar-wordpress/security/advisories/new. Only the maintainers can see your report.

Please include:

- What the problem is and where (file, feature or URL).
- The versions of WordPress, PHP and Ashlar you tested.
- Steps to reproduce, or a short proof of concept.
- The impact you think it has.

What to expect:

- We will acknowledge your report and keep you informed as we investigate.
- We will fix confirmed problems in a patch release and credit you in the changelog if you want.
- Please give us reasonable time to release a fix before you publish details.

Only the latest release of Ashlar and the latest WordPress and PHP security releases are supported.
