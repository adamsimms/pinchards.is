# pinchard.is

www.pinchards.is

**Layout**

| Area | Purpose |
|------|---------|
| **Core pages** | `index.php`, `gallery.php`, `info.php`, `slider.php`, `getphotos.php` at repo root (web document root). |
| **`lib/`** | `env.php` (local secrets file), `bootstrap.php` (AWS + S3 + `getObjectList`), `config.php` (bucket + CDN URLs). Core pages load `lib/bootstrap.php`; mini-sites still use `functions_inc.php`, which only forwards to `lib/bootstrap.php`. |
| **Public assets** | `css/`, `js/`, `images/` (site art + `images/photo/` for EXIF temp `tmp.jpg`, thumbnails, and local gallery assets), `fonts/`, `favicon/`, `vendor/`. |
| **Source / design** | Theme styles: edit `css/pinchard.css` directly. `design/` — Sketch/SVG sources (not served). |
| **Mini-sites** | `jam/` (S3 slideshows), `trees/` & `resettled/` (Google My Maps embeds + shared `lib/partials/microsite.php` shell), `waves/` (+ `wave.php` / `wave2.php` ERDDAP viz), `adrift/` (Three.js scene), `dory/` (Sketchfab embed), `light-house/` (Vimeo), `map/` (`index.php` Mapbox GL), `weather/` (`weather.php` JSON proxy; needs `RAPIDAPI_KEY` in `secrets.local.php`). |

## Local secrets on DreamHost

The PHP app reads **`AWS_ACCESS_KEY_ID`**, **`AWS_SECRET_ACCESS_KEY`**, optional **`GOOGLE_MAPS_API_KEY`**, **`MAPBOX_ACCESS_TOKEN`** (for `map/index.php`), and **`RAPIDAPI_KEY`** (for `weather/weather.php`) via `getenv()` / `$_ENV` (see `lib/env.php`).

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging (enables `display_errors`); production leaves errors in the server log.

**Recommended on DreamHost:** create a server-only file in the **site root** (same directory as `index.php`):

1. Copy `secrets.local.php.example` to **`secrets.local.php`** on the server (SFTP/SSH).
2. Edit `secrets.local.php` and replace the placeholders with your IAM user’s access key ID and secret (and any other keys you use).
3. Leave **`AWS_DEFAULT_REGION=us-east-1`** unless the bucket uses another region.

`lib/env.php` loads **`secrets.local.php`** when present (or legacy **`aws-env.local.php`** if you have not renamed yet). That file is listed in `.gitignore` and **denied by root `.htaccess`** so it should not be fetchable over the web.

**Alternatives:** If your plan exposes real environment variables to PHP (some VPS setups), you can set the same variable names there and omit `secrets.local.php`.

## Deploy on push (GitHub → FTP)

On **push to `main`**, [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml) runs and syncs the repo to your host with [FTP-Deploy-Action](https://github.com/SamKirkland/FTP-Deploy-Action).

In the GitHub repo: **Settings → Secrets and variables → Actions → New repository secret**, add:

| Secret | Example / notes |
|--------|------------------|
| `FTP_SERVER` | e.g. `ftp.dreamhost.com` or your domain’s FTP host from DreamHost |
| `FTP_USERNAME` | FTP user (often `you@yourdomain.com` style on DreamHost) |
| `FTP_PASSWORD` | That user’s password |
| `FTP_SERVER_DIR` | Remote directory for the site root, e.g. `pinchard.is` or `pinchards.is` (path is usually relative to the FTP user’s home) |

`secrets.local.php` (and legacy `aws-env.local.php`) are **excluded** from sync so they stay only on the server and are not deleted by deploys.

DreamHost’s usual FTP endpoint expects **plain FTP** on port **21**; **FTPS** often fails with `500 AUTH not understood`, so the workflow uses `protocol: ftp`. For hosts that require TLS, try `ftps` or `ftps-legacy` in [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml).

## Future hosting notes

- **Cost:** Revisit a small VPS (e.g. Hetzner / DigitalOcean droplet) as a potentially cheaper alternative to DreamHost for PHP + low traffic.
- **Cloudflare:** Static site + Pages Functions / Workers could replace PHP long term; needs a dedicated test pass before migrating.
