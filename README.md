# pinchard.is

www.pinchards.is

**Layout**

| Area | Purpose |
|------|---------|
| **Core pages** | `index.php`, `gallery.php`, `info.php`, `slider.php`, `getphotos.php` at repo root (web document root). |
| **`lib/`** | `bootstrap.php` (AWS + S3 + `getObjectList`), `config.php` (bucket + CDN URLs). Core pages load `lib/bootstrap.php`; mini-sites still use `functions_inc.php`, which only forwards to `lib/bootstrap.php`. |
| **Public assets** | `css/`, `js/`, `images/` (site art + `images/photo/` for EXIF temp `tmp.jpg`, thumbnails, and local gallery assets), `fonts/`, `favicon/`, `vendor/`. |
| **Source / design** | Theme styles: edit `css/pinchard.css` directly. `design/` — Sketch/SVG sources (not served). |
| **Mini-sites** | Self-contained folders (`jam/`, `trees/`, `resettled/`, `adrift/`, `waves/`, `bkp/`, …): deployed with the site, not second-class; leave their structure as-is unless you intentionally refactor them. |

## AWS credentials on DreamHost

The PHP app reads **`AWS_ACCESS_KEY_ID`** and **`AWS_SECRET_ACCESS_KEY`** via `getenv()` (AWS SDK default chain).

**Recommended on DreamHost:** create a server-only file in the **site root** (same directory as `index.php`):

1. Copy `aws-env.local.php.example` to **`aws-env.local.php`** on the server (SFTP/SSH).
2. Edit `aws-env.local.php` and replace the placeholders with your IAM user’s access key ID and secret.
3. Leave **`AWS_DEFAULT_REGION=us-east-1`** unless the bucket uses another region.

`lib/bootstrap.php` loads `aws-env.local.php` when present. That file is listed in `.gitignore` and **denied by root `.htaccess`** so it should not be fetchable over the web.

**Alternatives:** If your plan exposes real environment variables to PHP (some VPS setups), you can set the same three names there and omit `aws-env.local.php`.

## Deploy on push (GitHub → FTP)

On **push to `master`**, [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml) runs and syncs the repo to your host with [FTP-Deploy-Action](https://github.com/SamKirkland/FTP-Deploy-Action).

In the GitHub repo: **Settings → Secrets and variables → Actions → New repository secret**, add:

| Secret | Example / notes |
|--------|------------------|
| `FTP_SERVER` | e.g. `ftp.dreamhost.com` or your domain’s FTP host from DreamHost |
| `FTP_USERNAME` | FTP user (often `you@yourdomain.com` style on DreamHost) |
| `FTP_PASSWORD` | That user’s password |
| `FTP_SERVER_DIR` | Remote directory for the site root, e.g. `pinchard.is` or `pinchards.is` (path is usually relative to the FTP user’s home) |

`aws-env.local.php` is **excluded** from sync so it stays only on the server and is not deleted by deploys.

DreamHost’s usual FTP endpoint expects **plain FTP** on port **21**; **FTPS** often fails with `500 AUTH not understood`, so the workflow uses `protocol: ftp`. For hosts that require TLS, try `ftps` or `ftps-legacy` in [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml).

## Future hosting notes

- **Cost:** Revisit a small VPS (e.g. Hetzner / DigitalOcean droplet) as a potentially cheaper alternative to DreamHost for PHP + low traffic.
- **Cloudflare:** Static site + Pages Functions / Workers could replace PHP long term; needs a dedicated test pass before migrating.
