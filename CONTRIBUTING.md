# Contributing

Thanks for your interest in **pinchards.is**. This is a personal archive site, but small, focused improvements are welcome.

## Good first contributions

- Documentation fixes and clarifications
- Accessibility (keyboard nav, alt text, contrast, semantic HTML)
- Performance (image lazy-loading, CSS/JS cleanup)
- Bug fixes with a clear reproduction steps

## Before you start

1. **Search existing issues** — someone may already be working on it.
2. **Open an issue** for non-trivial changes so we can agree on approach before you invest time.
3. **Keep scope small** — one logical change per pull request.

## Development setup

See the [README Quick start](README.md#quick-start). You do not need production AWS credentials to edit templates, CSS, or static pages — only the S3-backed gallery and map features require keys.

```bash
composer install
npm install
npm run vendor:frontend
php -S localhost:8080
```

## Code conventions

Match the existing style in the files you touch:

- **PHP:** 8.1+, `declare(strict_types=1);` in new files, tabs for indentation, helper functions in `lib/helpers.php`.
- **CSS:** Edit `css/pinchard.css`; the site uses DM Sans via Google Fonts.
- **JavaScript:** Vanilla JS in `js/` (`pinchard.js`, `viewer.js`, `slideshow.js`, `gsap-motion.js`) — no jQuery. Keep mini-site scripts self-contained when they must differ from the main site.
- **Secrets:** Never commit `secrets.local.php` or API keys. Use placeholders in examples only.

## Pull requests

1. Fork the repo and create a branch from `main`.
2. Make your changes with a clear commit message (what and why).
3. Test locally if you can (especially PHP pages you modified).
4. Open a PR against `main` with:
   - A short summary of the change
   - How you tested it
   - Screenshots for visible UI changes

Maintainers deploy by merging to `main`; you do not need server access.

## What we are not looking for

- Large rewrites or framework migrations without prior discussion
- Changes to live photograph content or citation text without owner approval
- Adding heavy dependencies for minor convenience
- Commits that include vendored `vendor/` output (CI builds it)

## Questions

Open a [GitHub issue](https://github.com/adamsimms/pinchards.is/issues) or use the contact details on the live [About](https://www.pinchards.is/info.php) page.
