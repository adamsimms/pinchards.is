# pinchard.is

[![Deploy](https://github.com/adamsimms/pinchards.is/actions/workflows/deploy.yml/badge.svg)](https://github.com/adamsimms/pinchards.is/actions/workflows/deploy.yml)

[www.pinchards.is](https://www.pinchards.is) — **Cloudberry**, a long-running photographic archive of off-the-grid life on Pinchard's Island, Newfoundland: landscape, people, boats, seasons, and [...]

This repository is the website source — gallery, about page, maps, and exhibition pages that present those photographs online.

## Artist Statement

The simplicity of the Cloudberry photographs belies the complexity of the solar-powered camera system that makes them possible. A camera affixed to Adam’s family’s cabin on Pinchard’s Island takes the photographs continuously throughout the day and uploads them to the internet via cellular network. Able to control the camera and access the images from anywhere, Adam uses technology to maintain a constant presence in a place that is inaccessible most of the year. In capturing a view that recalls the experience of looking out the cabin’s window, the camera stands in for the photographer. Thus, these photographs constitute a political act: they allow an uninterrupted foothold on a place that was effectively erased when the Canadian government resettled the area shortly after Newfoundland became the country’s 10th province.

Comprised of thousands of photographs, the enormity of the Cloudberry project has inspired Adam to explore a new aspect of the sublime in his work. While other projects have focused on the pursuit of a single majestic photograph, Cloudberry inspires awe through its abundance of quotidian images. The title itself parallels this shift: while it may at first evoke the vast Newfoundland sky, Cloudberry in fact references the indigenous wild berry—colloquially known as a “bakeapple”—that flourishes on the seemingly inhospitable land.

The Cloudberry photographs are at once static and dynamic: though the frame remains exactly the same, the landscape is constantly changing. The enormous variety in the images recalls series of Impressionist paintings that show the same scene in different types of light, in all seasons, and throughout the day. The sky, the ocean, and the terrain in the photographs seem to change more readily than the immutable rocks, but even their appearance eventually transforms as the seasons change. Though Cloudberry highlights the specificity of this particular landscape, it also manifests the familiar desire to connect to one’s homeland not as a relic of the past but as a place that is alive.

## Contents

- [Quick start](#quick-start)
- [Project layout](#project-layout)
- [Local development](#local-development)
- [Deploy](#deploy)

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
| **Core pages** | `index.php`, `gallery.php`, `info.php`, `slideshow.php`, `viewer-photo.php` at repo root (web document root). Legacy `/gallery-days.php` and `/slider.php` redirect via `.htaccess`. |
| **`lib/`** | `bootstrap.php` (AWS + S3), `config.php` (bucket + CDN URLs), `env.php` (secrets loader). Pages and mini-sites load `lib/bootstrap.php` (or `lib/env.php` for maps-only pages). |
| **Public assets** | `css/`, `js/`, `images/`, `favicon/`. `vendor/` is generated locally and in CI (not committed) — Bootstrap CSS plus GSAP + ScrollTrigger via `npm run vendor:frontend`. |
| **Source / design** | Edit `css/pinchard.css` for theme styles. `design/` holds Sketch/SVG sources (not served). |
| **Mini-sites** | `jam/` (fullscreen exhibition slideshow), `maps/` (Mapbox satellite + Google My Maps embeds), `light-house/` (Vimeo). Legacy `/map/`, `/trees/`, `/resettled/` redirect to `/maps/…`. |
| **Scripts** | `scripts/vendor-frontend.js` copies Bootstrap/GSAP into `vendor/`. `scripts/cache-exif-dates.php` backfills capture dates from EXIF into `images/photo/.cache/` (needs AWS credentials). |
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

## Deploy

Production deploys run on push to `main` via [GitHub Actions](.github/workflows/deploy.yml) → rsync over SSH to DreamHost. The workflow runs `composer install --no-dev` and `npm ci && npm run ve[...]

See **[docs/DEPLOY.md](docs/DEPLOY.md)** for SSH key setup, repository secrets, and hosting notes.
