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

## Secrets (`secrets.local.php`)

The PHP app reads **`AWS_ACCESS_KEY_ID`**, **`AWS_SECRET_ACCESS_KEY`**, optional **`GOOGLE_MAPS_API_KEY`**, **`MAPBOX_ACCESS_TOKEN`** (for `map/index.php`), and **`RAPIDAPI_KEY`** (for `weather/weather.php`) via `getenv()` / `$_ENV` (see `lib/env.php`).

`lib/env.php` loads **`secrets.local.php`** when present (or legacy **`aws-env.local.php`**). That file is gitignored and **denied by root `.htaccess`** so it should not be fetchable over the web.

### Production (GitHub Actions → DreamHost)

On each deploy, [.github/scripts/write-secrets-local.php](.github/scripts/write-secrets-local.php) builds **`secrets.local.php`** from repository secrets and FTP uploads it with the rest of the site. Rotate keys in **GitHub → Settings → Secrets and variables → Actions**, then push to `main` or run the deploy workflow manually.

| Secret | Required | Used for |
|--------|----------|----------|
| `AWS_ACCESS_KEY_ID` | yes | S3 gallery (`lib/bootstrap.php`) |
| `AWS_SECRET_ACCESS_KEY` | yes | S3 gallery |
| `AWS_DEFAULT_REGION` | no (defaults to `us-east-1`) | S3 region |
| `GOOGLE_MAPS_API_KEY` | no | Photo map on `index.php` |
| `RAPIDAPI_KEY` | no | `weather/weather.php` |
| `MAPBOX_ACCESS_TOKEN` | no | `map/index.php` |

Deploy fails if the AWS pair is missing. Optional secrets are omitted from the generated file when unset.

### Local dev

Copy `secrets.local.php.example` to **`secrets.local.php`** in the repo root and fill in placeholders. Do not commit the real file.

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging (enables `display_errors`); production leaves errors in the server log.

**Alternatives:** DreamHost/VPS real environment variables with the same names, or a one-off SFTP upload of `secrets.local.php` (not needed if GitHub secrets are configured).

## Deploy on push (GitHub → FTP)

On **push to `main`**, [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml) runs and syncs the repo to your host with [FTP-Deploy-Action](https://github.com/SamKirkland/FTP-Deploy-Action).

In the GitHub repo: **Settings → Secrets and variables → Actions → New repository secret**, add:

| Secret | Example / notes |
|--------|------------------|
| `FTP_SERVER` | e.g. `ftp.dreamhost.com` or your domain’s FTP host from DreamHost |
| `FTP_USERNAME` | FTP user (often `you@yourdomain.com` style on DreamHost) |
| `FTP_PASSWORD` | That user’s password |
| `FTP_SERVER_DIR` | Remote directory for the site root, e.g. `pinchard.is` or `pinchards.is` (path is usually relative to the FTP user’s home) |
| `AWS_ACCESS_KEY_ID` | IAM access key for the S3 gallery bucket |
| `AWS_SECRET_ACCESS_KEY` | Matching secret |
| `AWS_DEFAULT_REGION` | Optional; omit to use `us-east-1` |
| `GOOGLE_MAPS_API_KEY` | Optional; browser key for `index.php` map |
| `RAPIDAPI_KEY` | Optional; Dark Sky proxy in `weather/` |
| `MAPBOX_ACCESS_TOKEN` | Optional; `pk.*` token for `map/` |

Runtime secrets are written to `secrets.local.php` during the workflow (not stored in git). After adding or rotating secrets, redeploy (push to `main` or **Actions → Deploy FTP → Run workflow**).

DreamHost’s usual FTP endpoint expects **plain FTP** on port **21**; **FTPS** often fails with `500 AUTH not understood`, so the workflow uses `protocol: ftp`. For hosts that require TLS, try `ftps` or `ftps-legacy` in [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml).

## Future hosting notes

- **Cost:** Revisit a small VPS (e.g. Hetzner / DigitalOcean droplet) as a potentially cheaper alternative to DreamHost for PHP + low traffic.
- **Cloudflare:** Static site + Pages Functions / Workers could replace PHP long term; needs a dedicated test pass before migrating.
