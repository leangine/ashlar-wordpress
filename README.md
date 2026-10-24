# Ashlar for WordPress

The free Ashlar block theme and the free Ashlar Kits plugin. Ashlar is a library of complete, checked site kits. This repository is the free, public part of the WordPress version.

Maker line: Ashlar, by Leangine.

## Layout

| Path | What |
|---|---|
| `theme/ashlar/` | Block theme. Layout, color and type come from `theme.json`. |
| `plugins/ashlar-kits/` | Plugin: block patterns, page templates, kit importer. Native blocks only. |
| `docs/` | Notes for this repository |
| `SECURITY.md` | Security rules and how to report a problem |
| `PERFORMANCE.md` | Performance rules, including Gutenberg and Elementor support |

Related, in separate repositories: the Elementor add-on (`leangine/ashlar-wordpress-elementor`) and the paid Ashlar Kits Pro add-on. They are kept separate so this theme and plugin carry no Elementor code and stay light. See `PERFORMANCE.md`.

## Requirements

- WordPress 7.0 or newer (developed on 7.1.3)
- PHP 8.2 or newer (developed on PHP 8.5; use the latest patch release, see `SECURITY.md`)

## Development

You need PHP 8.2 or newer, Composer and a local WordPress 7.0 or newer site (any local environment works). Link `theme/ashlar` into `wp-content/themes/` and `plugins/ashlar-kits` into `wp-content/plugins/`, then activate both.

```bash
composer install
composer run check    # coding standards with security sniffs, static analysis, known vulnerabilities
```

The maintainers develop in a Docker stack (nginx, PHP-FPM 8.5, MariaDB, Mailpit, WP-CLI) that is not part of this repository.

## Branches

| Branch | Purpose |
|---|---|
| `dev` | Daily work. Commit here. |
| `stage` | Pre-release testing. Updated from `dev` by pull request. |
| `main` | Released code. Updated from `stage` by pull request. |

Work on `dev`. Do not commit straight to `stage` or `main`. There is no remote yet; add one with `git remote add origin <url>` and push all three branches.

## Before every commit

1. `composer run check` is clean.
2. No secrets, keys or `.env` files are staged.
3. Every output is escaped, every input is sanitized, every form action has a nonce and a capability check (see `SECURITY.md`).
4. The change does not add a script or style to every page (see `PERFORMANCE.md`).

## License

Code: GPL-2.0-or-later (`LICENSE`). See `LICENSING.md`, `THIRD_PARTY.md` and `TRADEMARKS.md`. Contributions: `CONTRIBUTING.md`.
