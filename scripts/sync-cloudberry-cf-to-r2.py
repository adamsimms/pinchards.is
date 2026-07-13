#!/usr/bin/env python3
"""Sync Cloudberry JPEGs from CloudFront → Cloudflare R2 via REST API.

Source of truth for keys: images/photo/.cache/s3-list-*.json
Auth: Wrangler OAuth token from ~/.wrangler config (or CF_API_TOKEN env).

Usage:
  python3 scripts/sync-cloudberry-cf-to-r2.py thumbs
  python3 scripts/sync-cloudberry-cf-to-r2.py full
  python3 scripts/sync-cloudberry-cf-to-r2.py both
"""

from __future__ import annotations

import concurrent.futures
import json
import os
import sys
import threading
import time
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

ACCOUNT_ID = "1bf55fc4d05548d7bf541d845d3bcbb3"
REPO = Path(__file__).resolve().parents[1]
CACHE = REPO / "images" / "photo" / ".cache"

BUCKETS = {
    "full": {
        "bucket": "art-adamsimms-xyz-cloudberry-images",
        "cdn": "https://d3kq73uimqeic8.cloudfront.net/",
    },
    "thumbs": {
        "bucket": "art-adamsimms-xyz-cloudberry-thumbs",
        "cdn": "https://d35wkpjsrmtk40.cloudfront.net/",
    },
}

# R2 REST API: ≤1200 requests / 5 minutes — stay under ~3.5/sec with headroom.
MAX_WORKERS = 4
MIN_INTERVAL = 0.32


class RateLimiter:
    def __init__(self, min_interval: float) -> None:
        self.min_interval = min_interval
        self._lock = threading.Lock()
        self._next = 0.0

    def wait(self) -> None:
        with self._lock:
            now = time.monotonic()
            if now < self._next:
                time.sleep(self._next - now)
                now = time.monotonic()
            self._next = now + self.min_interval


def load_token() -> str:
    env = os.environ.get("CF_API_TOKEN")
    if env:
        return env
    cfg = Path.home() / "Library/Preferences/.wrangler/config/default.toml"
    if not cfg.exists():
        cfg = Path.home() / ".config/.wrangler/config/default.toml"
    for line in cfg.read_text().splitlines():
        if line.startswith("oauth_token"):
            return line.split("=", 1)[1].strip().strip('"')
    raise SystemExit("No CF_API_TOKEN / wrangler oauth_token found")


def load_filenames() -> list[str]:
    lists = sorted(CACHE.glob("s3-list-*.json"))
    if not lists:
        raise SystemExit(f"No s3-list cache under {CACHE}")
    data = json.loads(lists[-1].read_text())
    return [item["filename"] for item in data["items"]]


def api_request(
    token: str,
    method: str,
    url: str,
    data: bytes | None = None,
    headers: dict[str, str] | None = None,
) -> tuple[int, dict | list | None, bytes]:
    hdrs = {"Authorization": f"Bearer {token}"}
    if headers:
        hdrs.update(headers)
    req = urllib.request.Request(url, data=data, headers=hdrs, method=method)
    try:
        with urllib.request.urlopen(req, timeout=180) as resp:
            body = resp.read()
            code = resp.status
    except urllib.error.HTTPError as e:
        body = e.read()
        code = e.code
    parsed = None
    if body:
        try:
            parsed = json.loads(body.decode())
        except json.JSONDecodeError:
            parsed = None
    return code, parsed, body


def list_existing_keys(token: str, bucket: str, limiter: RateLimiter) -> set[str]:
    keys: set[str] = set()
    cursor = None
    while True:
        limiter.wait()
        qs = "per_page=1000"
        if cursor:
            qs += f"&cursor={urllib.parse.quote(cursor)}"
        url = (
            f"https://api.cloudflare.com/client/v4/accounts/{ACCOUNT_ID}"
            f"/r2/buckets/{bucket}/objects?{qs}"
        )
        code, parsed, _ = api_request(token, "GET", url)
        if code != 200 or not isinstance(parsed, dict) or not parsed.get("success"):
            raise SystemExit(f"list failed ({code}): {parsed}")
        result = parsed.get("result") or []
        for obj in result:
            if isinstance(obj, dict) and "key" in obj:
                keys.add(obj["key"])
            elif isinstance(obj, str):
                keys.add(obj)
        # Cursor may be in result_info
        info = parsed.get("result_info") or {}
        cursor = info.get("cursor") or info.get("continuation_token")
        if not cursor or not result:
            break
    return keys


def sync_one(
    token: str,
    bucket: str,
    cdn_base: str,
    filename: str,
    limiter: RateLimiter,
) -> tuple[str, str]:
    key_enc = urllib.parse.quote(filename, safe="")
    put_url = (
        f"https://api.cloudflare.com/client/v4/accounts/{ACCOUNT_ID}"
        f"/r2/buckets/{bucket}/objects/{key_enc}"
    )
    for attempt in range(5):
        try:
            with urllib.request.urlopen(cdn_base + urllib.parse.quote(filename), timeout=180) as resp:
                blob = resp.read()
                content_type = resp.headers.get("Content-Type") or "image/jpeg"
        except Exception as e:
            if attempt == 4:
                return filename, f"download-error: {e}"
            time.sleep(1.5 * (attempt + 1))
            continue

        limiter.wait()
        code, parsed, _ = api_request(
            token,
            "PUT",
            put_url,
            data=blob,
            headers={"Content-Type": content_type},
        )
        if code == 200 and isinstance(parsed, dict) and parsed.get("success"):
            return filename, "ok"
        if code in (429, 503, 502):
            time.sleep(2.5 * (attempt + 1))
            continue
        return filename, f"put-error {code}: {parsed}"
    return filename, "exhausted-retries"


def sync_kind(kind: str, token: str, filenames: list[str]) -> None:
    conf = BUCKETS[kind]
    bucket = conf["bucket"]
    cdn = conf["cdn"]
    limiter = RateLimiter(MIN_INTERVAL)

    print(f"\n=== {kind}: listing existing keys in {bucket} ===", flush=True)
    existing = list_existing_keys(token, bucket, limiter)
    todo = [f for f in filenames if f not in existing]
    print(f"existing={len(existing)} todo={len(todo)} total={len(filenames)}", flush=True)
    if not todo:
        print(f"{kind}: nothing to upload", flush=True)
        return

    ok = 0
    fail = 0
    t0 = time.time()
    with concurrent.futures.ThreadPoolExecutor(max_workers=MAX_WORKERS) as pool:
        futures = [
            pool.submit(sync_one, token, bucket, cdn, fn, limiter) for fn in todo
        ]
        for i, fut in enumerate(concurrent.futures.as_completed(futures), 1):
            fn, status = fut.result()
            if status == "ok":
                ok += 1
            else:
                fail += 1
                print(f"FAIL {fn}: {status}", flush=True)
            if i % 50 == 0 or i == len(todo):
                elapsed = time.time() - t0
                rate = i / elapsed if elapsed else 0
                print(
                    f"{kind}: {i}/{len(todo)} done ok={ok} fail={fail} "
                    f"({rate:.1f}/s)",
                    flush=True,
                )
    print(f"{kind}: finished ok={ok} fail={fail}", flush=True)
    if fail:
        raise SystemExit(1)


def main() -> None:
    if len(sys.argv) < 2 or sys.argv[1] not in ("full", "thumbs", "both"):
        raise SystemExit(__doc__)
    mode = sys.argv[1]
    token = load_token()
    filenames = load_filenames()
    print(f"filenames={len(filenames)} mode={mode}", flush=True)
    kinds = ["thumbs", "full"] if mode == "both" else [mode]
    for kind in kinds:
        sync_kind(kind, token, filenames)


if __name__ == "__main__":
    main()
