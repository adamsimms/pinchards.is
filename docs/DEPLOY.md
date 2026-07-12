# Deploy (GitHub Actions → DreamHost)

On **push to `main`**, [.github/workflows/deploy.yml](../.github/workflows/deploy.yml) syncs the repo to DreamHost with **rsync over SSH** (port 22). DreamHost [requires SFTP/SSH on port 22](https://help.dreamhost.com/hc/en-us/articles/115001051531-FTP-and-SFTP-at-DreamHost); plain FTP and FTPS are not used.

Before rsync, the workflow installs PHP dependencies (`composer install --no-dev`) and copies frontend libraries into `vendor/` (`npm ci && npm run vendor:frontend`). The `vendor/` directory is gitignored; only `vendor/.htaccess` is tracked.

The workflow excludes **`adrift/`**, **`dory/`**, and **`waves/`** on the server (separate repos: [adrift](https://github.com/adamsimms/adrift), [dory](https://github.com/adamsimms/dory), [waves](https://github.com/adamsimms/waves)).

Runtime secrets are uploaded to **`~/.config/pinchards.is/secrets.local.php`** on the server (one directory above the site root’s home folder, outside the web document root). Legacy `secrets.local.php` in the document root is removed on each deploy.

## One-time SSH key setup

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

## Repository secrets

| Secret | Example / notes |
|--------|------------------|
| `FTP_SERVER` | SSH hostname, e.g. `pinchards.is` or `ps12345.dreamhost.com` (from DreamHost panel) |
| `FTP_USERNAME` | DreamHost shell/FTP user |
| `FTP_SERVER_DIR` | **Absolute** site root on the server, e.g. `/home/USER/pinchards.is` |
| `SSH_DEPLOY_KEY` | Private key for deploy (see setup above) |
| `AWS_ACCESS_KEY_ID` | IAM access key for the S3 gallery bucket |
| `AWS_SECRET_ACCESS_KEY` | Matching secret |
| `AWS_DEFAULT_REGION` | Optional; omit to use `us-east-1` |
| `GOOGLE_MAPS_API_KEY` | Optional; browser key for `index.php` map |
| `MAPBOX_ACCESS_TOKEN` | Optional; `pk.*` token for `maps/` |

After adding or rotating secrets, redeploy (push to `main` or **Actions → Deploy → Run workflow**). Use **Run workflow → dry_run: true** first to list changes without uploading.

## Secrets on the server

The PHP app reads **`AWS_ACCESS_KEY_ID`**, **`AWS_SECRET_ACCESS_KEY`**, optional **`GOOGLE_MAPS_API_KEY`**, and **`MAPBOX_ACCESS_TOKEN`** via `getenv()` / `$_ENV` (see `lib/env.php`).

On each deploy, [.github/scripts/write-secrets-local.php](../.github/scripts/write-secrets-local.php) builds the secrets file from repository secrets and uploads it to **`$(dirname $FTP_SERVER_DIR)/.config/pinchards.is/secrets.local.php`**. That path is outside the web document root. Root `.htaccess` still denies HTTP access to `secrets.local.php` if a legacy copy exists.

| Secret | Required | Used for |
|--------|----------|----------|
| `AWS_ACCESS_KEY_ID` | yes | S3 gallery (`lib/bootstrap.php`) |
| `AWS_SECRET_ACCESS_KEY` | yes | S3 gallery |
| `AWS_DEFAULT_REGION` | no (defaults to `us-east-1`) | S3 region |
| `GOOGLE_MAPS_API_KEY` | no | Photo map on `index.php` |
| `MAPBOX_ACCESS_TOKEN` | no | `maps/` and index.php photo map |

Deploy fails if the AWS pair is missing. Optional secrets are omitted from the generated file when unset.

### Local dev

Copy `secrets.local.php.example` to **`secrets.local.php`** in the repo root and fill in placeholders. Do not commit the real file.

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging (enables `display_errors`); production leaves errors in the server log.

**Alternatives:** set `PINCHARD_SECRETS_FILE` to an absolute path, use `~/.config/pinchards.is/secrets.local.php`, or configure DreamHost/VPS environment variables with the same names.

## Future hosting notes

- **Cost:** Revisit a small VPS (e.g. Hetzner / DigitalOcean droplet) as a potentially cheaper alternative to DreamHost for PHP + low traffic.
- **Cloudflare:** Static site + Pages Functions / Workers could replace PHP long term; needs a dedicated test pass before migrating.

## PHP version (DreamHost)

DreamHost’s panel no longer offers PHP 8.1 (EOL). Use **PHP 8.3** (or 8.2+) for `www.pinchards.is` in DreamPanel → Manage Websites. Composer requires `>=8.2`; CI and deploy workflows run on 8.3. After changing the panel version, hit the viewer and gallery once to confirm EXIF and S3 still work.

## Cloudflare (edge)

Cloudflare sits in front of DreamHost. App-level headers live in root `.htaccess` (nosniff, frame options, referrer policy, **Content-Security-Policy-Report-Only**, CSS/JS cache). Prefer documenting panel changes here when you change them:

| Setting | Expected / notes |
|---------|------------------|
| SSL/TLS | Full (strict) once DreamHost has a valid cert |
| Always Use HTTPS | On |
| Auto Minify | Off for HTML if it ever mangles PHP output; CSS/JS minify optional |
| Cache Level | Standard; HTML from origin; static assets use short `Cache-Control` + `?v=` busting |
| Rocket Loader | Off (conflicts with Mapbox / gallery scripts) |
| Email Obfuscation / Scraping Protection | Optional; test About page if enabled |
| Analytics | Prefer Cloudflare Web Analytics (auto-inject) over app-side tags |
| Security headers | CSP is report-only in `.htaccess` first; promote to enforce after reviewing reports. HSTS is typically enabled in the Cloudflare SSL/TLS → Edge Certificates panel (not duplicated in `.htaccess`). |

Image bytes are served from CloudFront (`lib/config.php`), not from the DreamHost document root — Cloudflare mainly caches the HTML/CSS/JS shell.

## Disk / EXIF cache on the server

Full-resolution EXIF scratch files must not accumulate. Production uses `images/photo/.cache/exif-meta/` (small JSON) and deletes JPEGs after extraction. If an old `exif-tmp/` tree is still large on DreamHost, SSH in once and run:

```bash
cd /path/to/pinchards.is
php scripts/prune-exif-tmp.php --all
```

See [ARCHITECTURE.md](ARCHITECTURE.md) for the full cache layout.