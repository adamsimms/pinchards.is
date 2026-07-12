# Architecture

How Cloudberry serves photographs on [pinchards.is](https://www.pinchards.is).

## Request path

```
Browser → Cloudflare → DreamHost (PHP 8.2+) → S3 list / EXIF helpers
                              ↓
                    CloudFront (full + thumbnail JPEGs)
```

Photos are **not** in git. The repo is the PHP/CSS/JS shell; media lives in S3 buckets `shutter-island` (full) and `shutter-island-thumbnails`, fronted by CloudFront URLs in `lib/config.php`.

## Core data flow

1. **S3 object list** — `getObjectList()` in `lib/bootstrap.php` lists thumbnail keys, derives archive timestamps from filenames, and caches the list for 30 days under `images/photo/.cache/s3-list-*.json` (`lib/s3_cache.php`). Override with `PINCHARD_S3_LIST_CACHE_TTL` (seconds; `0` disables).
2. **Capture dates** — EXIF `DateTimeOriginal` overlays filename dates via `images/photo/.cache/exif-dates.json`. Backfill with `php scripts/cache-exif-dates.php`.
3. **Viewer metadata** — `pinchard_viewer_photo_payload()` in `lib/viewer.php` is the single source for camera/GPS/weather HTML used by `index.php` and `viewer-photo.php` (JSON API).
4. **EXIF extraction** — On cache miss, PHP downloads the full JPEG once, reads EXIF, writes a small JSON file under `images/photo/.cache/exif-meta/`, then **deletes** the JPEG. Leftover temps in `exif-tmp/` are pruned by TTL (default 1 hour) or `php scripts/prune-exif-tmp.php --all`.

## Page map

| Surface | Entry | Notes |
|---------|-------|--------|
| Photo viewer | `index.php` | Timeline + detail drawer; Mapbox when token present |
| Day gallery | `gallery.php` + `js/gallery.js` | Filmstrip (desktop) / vertical days (mobile) |
| About | `info.php` | Narrative + embeds |
| Slideshow | `slideshow.php` | Autoplay surface |
| JSON photo API | `viewer-photo.php` | Rate-limited; powers in-viewer navigation |
| Maps / light-house / jam | `maps/`, `light-house/`, `jam/` | Mini-sites; jam is `noindex` |

## Secrets

Loaded by `lib/env.php` in order: `PINCHARD_SECRETS_FILE` → `~/.config/pinchards.is/secrets.local.php` → repo-root `secrets.local.php`. Deploy uploads the production file **outside** the document root (see [DEPLOY.md](DEPLOY.md)).

## Caching layers

| Layer | What | TTL / busting |
|-------|------|----------------|
| Cloudflare / browser | CSS/JS | 1 day + `?v=mtime` query strings |
| S3 list JSON | Bucket keys | 30 days |
| EXIF dates JSON | Capture times | Persistent until rewritten |
| EXIF meta JSON | Full EXIF blobs | Persistent; tiny vs JPEGs |
| EXIF temp JPEGs | Download scratch | Deleted after read; TTL prune for leftovers |
| `viewer-photo.php` | JSON responses | `Cache-Control: max-age=300` |

## Product boundary

This site is a **closed archive** product. Artistic research in `docs/practice-*.md` may inform future sibling work; it is not an engineering backlog to turn Cloudberry into a remix/CMS platform. See [PRODUCT.md](PRODUCT.md).
