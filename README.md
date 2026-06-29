# pinchard.is

www.pinchards.is

**Layout**

| Area | Purpose |
|------|---------|
| **Core pages** | `index.php`, `gallery.php`, `info.php`, `slider.php`, `getphotos.php` at repo root (web document root). |
| **`lib/`** | `env.php` (local secrets file), `bootstrap.php` (AWS + S3 + `getObjectList`), `config.php` (bucket + CDN URLs). Core pages load `lib/bootstrap.php`; mini-sites still use `functions_inc.php`, which only forwards to `lib/bootstrap.php`. |
| **Public assets** | `css/`, `js/`, `images/` (site art + `images/photo/` for EXIF temp `tmp.jpg`, thumbnails, and local gallery assets), `fonts/`, `favicon/`, `vendor/`. |
| **Source / design** | Theme styles: edit `css/pinchard.css` directly. `design/` — Sketch/SVG sources (not served). |
| **Mini-sites** | `jam/` (S3 slideshows), `trees/` & `resettled/` (Google My Maps embeds + shared `lib/partials/microsite.php` shell), `waves/` (+ `wave.php` / `wave2.php` ERDDAP viz), `dory/` (Sketchfab embed), `light-house/` (Vimeo), `map/` (`index.php` Mapbox GL). [Adrift](https://github.com/adamsimms/adrift) (Three.js scene at `/adrift/`) is a separate repo with its own deploy. |

## Secrets (`secrets.local.php`)

The PHP app reads **`AWS_ACCESS_KEY_ID`**, **`AWS_SECRET_ACCESS_KEY`**, optional **`GOOGLE_MAPS_API_KEY`**, and **`MAPBOX_ACCESS_TOKEN`** (for `map/index.php`) via `getenv()` / `$_ENV` (see `lib/env.php`).

`lib/env.php` loads **`secrets.local.php`** when present (or legacy **`aws-env.local.php`**). That file is gitignored and **denied by root `.htaccess`** so it should not be fetchable over the web.

### Production (GitHub Actions → DreamHost)

On each deploy, [.github/scripts/write-secrets-local.php](.github/scripts/write-secrets-local.php) builds **`secrets.local.php`** from repository secrets and uploads it with the rest of the site over **SFTP/rsync (SSH port 22)**. The workflow excludes **`adrift/`** on the server (owned by [adamsimms/adrift](https://github.com/adamsimms/adrift)). Rotate keys in **GitHub → Settings → Secrets and variables → Actions**, then push to `main` or run the deploy workflow manually.

| Secret | Required | Used for |
|--------|----------|----------|
| `AWS_ACCESS_KEY_ID` | yes | S3 gallery (`lib/bootstrap.php`) |
| `AWS_SECRET_ACCESS_KEY` | yes | S3 gallery |
| `AWS_DEFAULT_REGION` | no (defaults to `us-east-1`) | S3 region |
| `GOOGLE_MAPS_API_KEY` | no | Photo map on `index.php` |
| `MAPBOX_ACCESS_TOKEN` | no | `map/index.php` |

Deploy fails if the AWS pair is missing. Optional secrets are omitted from the generated file when unset.

### Local dev

Copy `secrets.local.php.example` to **`secrets.local.php`** in the repo root and fill in placeholders. Do not commit the real file.

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging (enables `display_errors`); production leaves errors in the server log.

**Alternatives:** DreamHost/VPS real environment variables with the same names, or a one-off SFTP upload of `secrets.local.php` (not needed if GitHub secrets are configured).

## Deploy on push (GitHub → SFTP)

On **push to `main`**, [.github/workflows/deploy-ftp.yml](.github/workflows/deploy-ftp.yml) runs and syncs the repo to DreamHost with **rsync over SSH** (port 22). DreamHost [requires SFTP/SSH on port 22](https://help.dreamhost.com/hc/en-us/articles/115001051531-FTP-and-SFTP-at-DreamHost); plain FTP and FTPS (`500 AUTH not understood`) are not used.

### One-time SSH key setup (manual)

1. On your Mac, generate a deploy-only key (no passphrase so Actions can use it):
   ```bash
   ssh-keygen -t ed25519 -C "github-deploy-pinchards" -f ~/.ssh/pinchards_deploy -N ""
   ```
2. Append the public key to the server (enter the site user password once):
   ```bash
   cat ~/.ssh/pinchards_deploy.pub | ssh YOUR_USER@YOUR_SERVER "mkdir -p ~/.ssh && chmod 700 ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && chmod go-w ~"
   ```
3. Confirm key-only SSH works (use your DreamHost **server hostname** from the panel — often `iad1-shared-….dreamhost.com`, not `ftp.dreamhost.com`):
   ```bash
   ssh -i ~/.ssh/pinchards_deploy -o IdentitiesOnly=yes YOUR_USER@YOUR_SERVER "echo ok"
   ```
4. In **GitHub → Settings → Secrets and variables → Actions**, add or update secrets (see table below). For `SSH_DEPLOY_KEY`, base64-encode the private key so GitHub preserves newlines:
   ```bash
   base64 < ~/.ssh/pinchards_deploy | pbcopy
   ```
   Paste that **single line** as `SSH_DEPLOY_KEY` (not the `.pub` file).

### Repository secrets

| Secret | Example / notes |
|--------|------------------|
| `FTP_SERVER` | SSH hostname, e.g. `pinchards.is` or `ps12345.dreamhost.com` (from DreamHost panel) |
| `FTP_USERNAME` | DreamHost shell/FTP user |
| `FTP_SERVER_DIR` | **Absolute** site root on the server, e.g. `/home/USER/pinchards.is` |
| `SSH_DEPLOY_KEY` | Private key for deploy (see setup above); `FTP_PASSWORD` is no longer used |
| `AWS_ACCESS_KEY_ID` | IAM access key for the S3 gallery bucket |
| `AWS_SECRET_ACCESS_KEY` | Matching secret |
| `AWS_DEFAULT_REGION` | Optional; omit to use `us-east-1` |
| `GOOGLE_MAPS_API_KEY` | Optional; browser key for `index.php` map |
| `MAPBOX_ACCESS_TOKEN` | Optional; `pk.*` token for `map/` |

Runtime secrets are written to `secrets.local.php` during the workflow (not stored in git). After adding or rotating secrets, redeploy (push to `main` or **Actions → Deploy SFTP → Run workflow**). Use **Run workflow → dry_run: true** first to list changes without uploading.

## Future hosting notes

- **Cost:** Revisit a small VPS (e.g. Hetzner / DigitalOcean droplet) as a potentially cheaper alternative to DreamHost for PHP + low traffic.
- **Cloudflare:** Static site + Pages Functions / Workers could replace PHP long term; needs a dedicated test pass before migrating.
