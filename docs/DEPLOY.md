# Deploy (GitHub Actions → DreamHost)

On **push to `main`**, [.github/workflows/deploy-ftp.yml](../.github/workflows/deploy-ftp.yml) syncs the repo to DreamHost with **rsync over SSH** (port 22). DreamHost [requires SFTP/SSH on port 22](https://help.dreamhost.com/hc/en-us/articles/115001051531-FTP-and-SFTP-at-DreamHost); plain FTP and FTPS are not used.

Before rsync, the workflow installs PHP dependencies (`composer install --no-dev`) and copies frontend libraries into `vendor/` (`npm ci && npm run vendor:frontend`). The `vendor/` directory is gitignored; only `vendor/.htaccess` is tracked.

The workflow excludes **`adrift/`**, **`dory/`**, and **`waves/`** on the server (separate repos: [adrift](https://github.com/adamsimms/adrift), [dory](https://github.com/adamsimms/dory), [waves](https://github.com/adamsimms/waves)).

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
| `SSH_DEPLOY_KEY` | Private key for deploy (see setup above); `FTP_PASSWORD` is no longer used |
| `AWS_ACCESS_KEY_ID` | IAM access key for the S3 gallery bucket |
| `AWS_SECRET_ACCESS_KEY` | Matching secret |
| `AWS_DEFAULT_REGION` | Optional; omit to use `us-east-1` |
| `GOOGLE_MAPS_API_KEY` | Optional; browser key for `index.php` map |
| `MAPBOX_ACCESS_TOKEN` | Optional; `pk.*` token for `maps/` |
| `GOATCOUNTER_SITE_CODE` | Optional; analytics (see README) |
| `CLOUDFLARE_WEB_ANALYTICS_TOKEN` | Optional; analytics (see README) |

Runtime secrets are written to `secrets.local.php` during the workflow (not stored in git). After adding or rotating secrets, redeploy (push to `main` or **Actions → Deploy SFTP → Run workflow**). Use **Run workflow → dry_run: true** first to list changes without uploading.

## Secrets on the server

The PHP app reads **`AWS_ACCESS_KEY_ID`**, **`AWS_SECRET_ACCESS_KEY`**, optional **`GOOGLE_MAPS_API_KEY`**, and **`MAPBOX_ACCESS_TOKEN`** via `getenv()` / `$_ENV` (see `lib/env.php`).

On each deploy, [.github/scripts/write-secrets-local.php](../.github/scripts/write-secrets-local.php) builds **`secrets.local.php`** from repository secrets. That file is gitignored and **denied by root `.htaccess`**.

| Secret | Required | Used for |
|--------|----------|----------|
| `AWS_ACCESS_KEY_ID` | yes | S3 gallery (`lib/bootstrap.php`) |
| `AWS_SECRET_ACCESS_KEY` | yes | S3 gallery |
| `AWS_DEFAULT_REGION` | no (defaults to `us-east-1`) | S3 region |
| `GOOGLE_MAPS_API_KEY` | no | Photo map on `index.php` |
| `MAPBOX_ACCESS_TOKEN` | no | `maps/` and index.php photo map |
| `GOATCOUNTER_SITE_CODE` | no | Optional analytics |
| `CLOUDFLARE_WEB_ANALYTICS_TOKEN` | no | Optional analytics |

Deploy fails if the AWS pair is missing. Optional secrets are omitted from the generated file when unset.

### Local dev

Copy `secrets.local.php.example` to **`secrets.local.php`** in the repo root and fill in placeholders. Do not commit the real file.

Set **`PINCHARD_DEBUG=1`** in `secrets.local.php` only when debugging (enables `display_errors`); production leaves errors in the server log.

**Alternatives:** DreamHost/VPS real environment variables with the same names, or a one-off SFTP upload of `secrets.local.php`.

## Future hosting notes

- **Cost:** Revisit a small VPS (e.g. Hetzner / DigitalOcean droplet) as a potentially cheaper alternative to DreamHost for PHP + low traffic.
- **Cloudflare:** Static site + Pages Functions / Workers could replace PHP long term; needs a dedicated test pass before migrating.
