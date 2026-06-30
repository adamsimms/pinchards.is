# pinchard.is

[![Deploy SFTP](https://github.com/adamsimms/pinchards.is/actions/workflows/deploy.yml/badge.svg)](https://github.com/adamsimms/pinchards.is/actions/workflows/deploy.yml)

[www.pinchards.is](https://www.pinchards.is) — **Cloudberry**, an autonomous solar-powered camera on Pinchard's Island, Newfoundland. For years it photographed the same view from a remote cabin every daylight hour — off-the-grid life, landscape, and seasons — and this site is the archive.

This repository is the website source — gallery, about page, maps, and exhibition pages that present those photographs online.

## Contents

- [Quick start](#quick-start)
- [Project layout](#project-layout)
- [Local development](#local-development)
- [Deploy](#deploy)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Quick start

For a read-only tour of the code, clone and browse — no secrets needed to read PHP, CSS, and templates.

To run the gallery locally you need AWS credentials for the S3 bucket:

```bash
git clone https://github.com/adamsimms/pinchards.is.git
cd pinchards.is
cp secrets.local.php.example secrets.local.php   # add AWS keys (see file comments)
composer install && npm install && npm run vendor:frontend
php -S localhost:8080
```

Open [http://localhost:8080](http://localhost:8080). Map pages also need optional tokens in `secrets.local.php`.

## Project layout

| Area | Purpose |
|------|---------|
| **Core pages** | `index.php`, `gallery.php`, `info.php`, `slider.php`, `slideshow.php`, `getphotos.php` at repo root (web document root). |
| **`lib/`** | `bootstrap.php` (AWS + S3), `config.php` (bucket + CDN URLs), `env.php` (secrets loader). Core pages load `lib/bootstrap.php`; mini-sites use `functions_inc.php`, a shim to the same bootstrap. |
| **Public assets** | `css/`, `js/`, `images/`, `favicon/`. `vendor/` is generated locally and in CI (not committed). |
| **Source / design** | Edit `css/pinchard.css` for theme styles. `design/` holds Sketch/SVG sources (not served). |
| **Mini-sites** | `jam/` (fullscreen exhibition slideshow), `maps/` (Mapbox satellite + Google My Maps embeds), `light-house/` (Vimeo). Legacy `/map/`, `/trees/`, `/resettled/` redirect to `/maps/…`. |
| **Docs** | [docs/DEPLOY.md](docs/DEPLOY.md) — SSH keys, GitHub Actions secrets, hosting notes. |

Related projects on the live server (separate repos): [Adrift](https://github.com/adamsimms/adrift), [Dory](https://github.com/adamsimms/dory), [Waves](https://github.com/adamsimms/waves).

## Local development

**Requirements:** PHP 8.1+, Composer, Node.js 20+ (for frontend vendor copies).

1. Clone the repo.
2. Copy `secrets.local.php.example` → `secrets.local.php` in the repo root and add AWS keys (required for gallery) plus optional map tokens.
3. Build `vendor/` (PHP + JS/CSS):

   ```bash
   composer install
   npm install
   npm run vendor:frontend
   ```

   Or run `npm run vendor` after `npm install` (runs Composer + frontend copy in one step).

4. Serve the repo root with any PHP-capable web server, or use DreamHost-style document root pointing at this directory.

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging locally.

### Secrets file locations

The app loads the first readable file from this list (see `lib/env.php`):

1. `PINCHARD_SECRETS_FILE` environment variable (absolute path)
2. `~/.config/pinchards.is/secrets.local.php` (production — outside the web root)
3. `secrets.local.php` in the repo root (local dev)
4. `aws-env.local.php` in the repo root (legacy)

## Deploy

Production deploys run on push to `main` via [GitHub Actions](.github/workflows/deploy.yml) → rsync over SSH to DreamHost. The workflow runs `composer install --no-dev` and `npm ci && npm run vendor:frontend` before upload, so `vendor/` is built in CI rather than stored in git. Runtime secrets are uploaded to **`~/.config/pinchards.is/secrets.local.php`** on the server (outside the document root), not into the public web tree.

See **[docs/DEPLOY.md](docs/DEPLOY.md)** for SSH key setup, repository secrets, and hosting notes.

## Contributing

Bug fixes, accessibility improvements, and documentation updates are welcome. Please read **[CONTRIBUTING.md](CONTRIBUTING.md)** before opening a pull request.

## Security

To report a vulnerability, see **[SECURITY.md](SECURITY.md)**. Do not open public issues for credential leaks or exploitable bugs.

## License

Code in this repository is [MIT](LICENSE). Photographs and embedded media are not covered — see the live [About](https://www.pinchards.is/info.php) page for citation and contact.

Fonts: [DM Sans](https://fonts.google.com/specimen/DM+Sans) (Google Fonts, OFL). Vendored libraries retain their own licenses under `vendor/`.

## Analytics

Google Analytics was removed. Stats use [Cloudflare Web Analytics](https://www.cloudflare.com/web-analytics/) (free, cookieless), injected automatically at the edge when the domain is proxied through Cloudflare. Enable it in the Cloudflare dashboard under **Analytics → Web analytics → Manage site → Enable** — no code or repository secrets required.
