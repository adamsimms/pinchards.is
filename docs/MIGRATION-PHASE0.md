# Phase 0 inventory (2026-07-13)

Findings from starting Phase 0 + 1 of the Cloudflare migration.

## Counts

| Artifact | Location | Count |
|----------|----------|-------|
| Thumbnail/full keys | `images/photo/.cache/s3-list-*.json` | **1,652** |
| Capture dates | `exif-dates.json` → `dates` | **1,652** |
| Weather hours | `weather-hours.json` → `hours` | **4,752** |
| Per-photo EXIF meta | `exif-meta/*.json` | **6** (lazy/partial) |

Span from filenames: 2017-08-16 → 2018-03-01.

## Viewer drawer EXIF fields (Phase 2 implication)

[`lib/viewer.php`](../lib/viewer.php) renders **per-photo** (not only constants):

- Make, Model
- Focal length
- Exposure time, f-number, ISO
- Image width × height
- Resolution (ppi)
- GPS (or cabin defaults via `pinchard_cloudberry_gps_defaults()`)
- Weather HTML from hour lookup

Because `exif-meta/` only has 6 files, Phase 2 needs a **one-time EXIF completion pass** over full JPEGs in R2 (or from CloudFront during catalog build). Dates + weather can come from committed caches.

## Cloudflare / art stack

| Item | Status |
|------|--------|
| Account | `adamsimms.xyz` (`1bf55fc4d05548d7bf541d845d3bcbb3`) via Wrangler OAuth `hello@adamsimms.xyz` |
| Existing R2 | `art-adamsimms-xyz` → `media.adamsimms.xyz` |
| Pages | `art-adamsimms-xyz` → `art.adamsimms.xyz` (live 200) |
| Staging | Use Pages preview / `*.pages.dev` on art repo (`main`) |
| Node for Wrangler | Prefer Node 24 via nvm (`wrangler@4` requires ≥22) |

## Sibling API endpoints (later phases)

| App | Endpoint | Upstream |
|-----|----------|----------|
| Adrift | `weather.php` | MSC GeoMet `https://api.weather.gc.ca` (citypage + marine) |
| Waves | `call-api.php` / `health.php` | SmartAtlantic ERDDAP `https://www.smartatlantic.ca/erddap/tabledap/SMA_st_johns.json` |

## AWS access note

Local machine has **no AWS CLI** and no rclone S3 remotes. Sync uses **public CloudFront**:

- Full: `https://d3kq73uimqeic8.cloudfront.net/{filename}`
- Thumbs: `https://d35wkpjsrmtk40.cloudfront.net/{filename}`

GitHub Actions secrets still hold `AWS_*` for DreamHost-era workflows.

## Redirect map

Canonical targets are in the migration plan URL map. Source inventory from `.htaccess` + PHP entrypoints still applies; no change.

## Vanity share aliases

Deferred decision at Phase 5 cutover (`cloudberry.` → `/cloudberry/archive`).

## Phase 1 result (same day)

| Resource | Status |
|----------|--------|
| R2 `art-adamsimms-xyz-cloudberry-images` | Created; **1,652** objects |
| R2 `art-adamsimms-xyz-cloudberry-thumbs` | Created; **1,652** objects |
| `cloudberry-images.adamsimms.xyz` | Active SSL; spot-checks 200 |
| `cloudberry-thumbs.adamsimms.xyz` | Active SSL; spot-checks 200 |
| Sync script | [`scripts/sync-cloudberry-cf-to-r2.py`](../scripts/sync-cloudberry-cf-to-r2.py) (CloudFront → R2 REST; resumable) |

Optional 1b (point live PHP CDN at R2) **not done** — DreamHost still uses CloudFront until Phase 3+.

## Phase 2 notes

Viewer drawer fields confirmed per-photo (`lib/viewer.php`). Catalog builder: [`scripts/build-catalog.php`](../scripts/build-catalog.php).

- Completes EXIF via **Range** GET (`bytes=0-262143`) from `cloudberry-images.adamsimms.xyz` (no full JPEG download).
- Joins `exif-dates.json` + `weather-hours.json`.
- Emits structured [`data/catalog.json`](../data/catalog.json) (no HTML fragments) with new CDN URLs.
- GPS: archive photos typically lack EXIF GPS → cabin defaults (same as live PHP).

## Phase 3 notes

- [`scripts/build-static-archive.php`](../scripts/build-static-archive.php) → `dist-archive/` (gitignored) for art assemble.
- [`js/viewer.js`](../js/viewer.js) supports `catalogUrl` + `basePath` (DreamHost still uses `viewer-photo.php`).
- Art site: `npm run build:full` assembles into `dist/cloudberry/archive/`; maps live at `/maps`.
- Docs on art: `docs/CLOUDBERRY-ASSEMBLE.md`.
