# Contributing

Thank you for helping. Small, focused changes are easiest to review.

## Before you start

- Open an issue to discuss larger changes first.
- Security problems: do not open a public issue. Follow `SECURITY.md`.

## How

1. Fork the repository and branch from `dev`.
2. Make the change. Keep to the WordPress Coding Standards and the rules in `SECURITY.md` and `PERFORMANCE.md`: escape output, sanitize input, nonce and capability on every change, no scripts or styles on every page.
3. Run `composer install` and `composer run check`. It must be clean.
4. Sign off every commit with `git commit -s`. That adds a `Signed-off-by` line and means you agree to the Developer Certificate of Origin (https://developercertificate.org): you wrote the change or have the right to submit it under this project's license.
5. Open a pull request into `dev` with a clear description and, for visible changes, a screenshot.

## License of contributions

By contributing you agree your work is licensed under GPL-2.0-or-later, the license of this project (`LICENSE`). Do not add code or assets you cannot license that way. Do not add third-party code without listing it in `THIRD_PARTY.md`.

## Commit messages

Short and clear, with a type prefix: `feat:`, `fix:`, `docs:`, `chore:`, `refactor:`, `test:`. Explain why, not only what.
