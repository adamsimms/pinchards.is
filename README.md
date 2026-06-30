# pinchard.is

[www.pinchards.is](https://www.pinchards.is) — **Cloudberry**, an archived off-the-grid photography project on Pinchard's Island, Newfoundland.

PHP site: S3 photo gallery, about page, and mini-sites (maps, exhibition slideshow, video). Photographs are served from S3/CloudFront; this repo is the application code and static assets.

## Layout

| Area | Purpose |
|------|---------|
| **Core pages** | `index.php`, `gallery.php`, `info.php`, `slider.php`, `slideshow.php`, `getphotos.php` at repo root (web document root). |
| **`lib/`** | `bootstrap.php` (AWS + S3), `config.php` (bucket + CDN URLs), `env.php` (local secrets). Core pages load `lib/bootstrap.php`; mini-sites use `functions_inc.php`, a shim to the same bootstrap. |
| **Public assets** | `css/`, `js/`, `images/`, `favicon/`. `vendor/` is generated locally and in CI (not committed). |
| **Source / design** | Edit `css/pinchard.css` for theme styles. `design/` holds Sketch/SVG sources (not served). |
| **Mini-sites** | `jam/` (fullscreen exhibition slideshow), `maps/` (Mapbox satellite + Google My Maps embeds), `light-house/` (Vimeo). Legacy `/map/`, `/trees/`, `/resettled/` redirect to `/maps/…`. |

Related projects on the live server (separate repos): [Adrift](https://github.com/adamsimms/adrift), [Dory](https://github.com/adamsimms/dory), [Waves](https://github.com/adamsimms/waves).

## Local development

**Requirements:** PHP 8.1+, Composer, Node.js 20+ (for frontend vendor copies).

1. Clone the repo.
2. Copy `secrets.local.php.example` → `secrets.local.php` and add AWS keys (required for gallery) plus optional map tokens.
3. Build `vendor/` (PHP + JS/CSS):

   ```bash
   composer install
   npm install
   npm run vendor:frontend
   ```

   Or run `npm run vendor` after `npm install` (runs Composer + frontend copy in one step).

4. Serve the repo root with any PHP-capable web server, or use DreamHost-style document root pointing at this directory.

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging locally.

## Deploy

Production deploys run on push to `main` via GitHub Actions → rsync over SSH to DreamHost. The workflow runs `composer install --no-dev` and `npm ci && npm run vendor:frontend` before upload, so `vendor/` is built in CI rather than stored in git. Secrets are injected at deploy time as `secrets.local.php` (never committed).

See **[docs/DEPLOY.md](docs/DEPLOY.md)** for SSH key setup, repository secrets, and hosting notes.

## License

Code in this repository is [MIT](LICENSE). Photographs and embedded media are not covered — see the live [About](https://www.pinchards.is/info.php) page for citation and contact.

Fonts: [DM Sans](https://fonts.google.com/specimen/DM+Sans) (Google Fonts, OFL). Vendored libraries retain their own licenses under `vendor/`.

## Analytics

Google Analytics was removed. Stats use [Cloudflare Web Analytics](https://www.cloudflare.com/web-analytics/) (free, cookieless), injected automatically at the edge when the domain is proxied through Cloudflare. Enable it in the Cloudflare dashboard under **Analytics → Web analytics → Manage site → Enable** — no code or repository secrets required.
