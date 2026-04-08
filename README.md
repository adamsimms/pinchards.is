# pinchard.is

www.pinchards.is

## What’s in this repo

**Core site:** root PHP (`index.php`, `gallery.php`, `info.php`, …), `css/`, `js/` (built/min assets only), `img/`, `photo/`, `vendor/`, `favicon/`, `fonts/`, `less/` (source for styles), `source/` (design assets).

**Related sections (same stack, subpaths):** `jam/`, `trees/`, `resettled/`.

Standalone experiments (3D viewers, wave sims, old backups) were removed to keep the tree small; they still exist in git history before the cleanup commit if you ever need them.

## AWS credentials on DreamHost

The PHP app reads **`AWS_ACCESS_KEY_ID`** and **`AWS_SECRET_ACCESS_KEY`** via `getenv()` (AWS SDK default chain).

**Recommended on DreamHost:** create a server-only file next to `functions_inc.php`:

1. Copy `aws-env.local.php.example` to **`aws-env.local.php`** on the server (SFTP/SSH).
2. Edit `aws-env.local.php` and replace the placeholders with your IAM user’s access key ID and secret.
3. Leave **`AWS_DEFAULT_REGION=us-east-1`** unless the bucket uses another region.

`functions_inc.php` loads `aws-env.local.php` automatically if it exists. That file is listed in `.gitignore` and **denied by `.htaccess`** so it should not be fetchable over the web.

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
