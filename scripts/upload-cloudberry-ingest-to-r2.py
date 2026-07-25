#!/usr/bin/env python3
"""Upload staged Cloudberry ingest files (full + thumbs) to R2 via REST API.

Reads ~/Downloads/cloudberry-ingest/manifest.json and uploads each key from
full/ and thumbs/ into the Cloudberry R2 buckets.

Auth: Wrangler OAuth token (or CF_API_TOKEN).

Usage:
  python3 scripts/upload-cloudberry-ingest-to-r2.py
  python3 scripts/upload-cloudberry-ingest-to-r2.py --dir ~/Downloads/cloudberry-ingest
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
BUCKET_FULL = "art-adamsimms-xyz-cloudberry-images"
BUCKET_THUMBS = "art-adamsimms-xyz-cloudberry-thumbs"
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


def api_request(
    token: str,
    method: str,
    url: str,
    data: bytes | None = None,
    headers: dict[str, str] | None = None,
) -> tuple[int, dict | list | None]:
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
    return code, parsed


def put_file(
    token: str,
    bucket: str,
    key: str,
    path: Path,
    limiter: RateLimiter,
) -> tuple[str, str]:
    key_enc = urllib.parse.quote(key, safe="")
    put_url = (
        f"https://api.cloudflare.com/client/v4/accounts/{ACCOUNT_ID}"
        f"/r2/buckets/{bucket}/objects/{key_enc}"
    )
    blob = path.read_bytes()
    for attempt in range(5):
        limiter.wait()
        code, parsed = api_request(
            token,
            "PUT",
            put_url,
            data=blob,
            headers={"Content-Type": "image/jpeg"},
        )
        if code == 200 and isinstance(parsed, dict) and parsed.get("success"):
            return key, "ok"
        if code in (429, 502, 503):
            time.sleep(2.5 * (attempt + 1))
            continue
        return key, f"put-error {code}: {parsed}"
    return key, "exhausted-retries"


def upload_kind(
    label: str,
    token: str,
    bucket: str,
    base: Path,
    keys: list[str],
) -> None:
    limiter = RateLimiter(MIN_INTERVAL)
    print(f"\n=== {label}: uploading {len(keys)} → {bucket} ===", flush=True)
    ok = 0
    fail = 0
    t0 = time.time()
    with concurrent.futures.ThreadPoolExecutor(max_workers=MAX_WORKERS) as pool:
        futures = [
            pool.submit(put_file, token, bucket, key, base / key, limiter)
            for key in keys
        ]
        for i, fut in enumerate(concurrent.futures.as_completed(futures), 1):
            key, status = fut.result()
            if status == "ok":
                ok += 1
            else:
                fail += 1
                print(f"FAIL {key}: {status}", flush=True)
            if i % 25 == 0 or i == len(keys):
                elapsed = time.time() - t0
                rate = i / elapsed if elapsed else 0
                print(
                    f"{label}: {i}/{len(keys)} ok={ok} fail={fail} ({rate:.1f}/s)",
                    flush=True,
                )
    print(f"{label}: finished ok={ok} fail={fail}", flush=True)
    if fail:
        raise SystemExit(1)


def main() -> None:
    ingest = Path.home() / "Downloads" / "cloudberry-ingest"
    for arg in sys.argv[1:]:
        if arg.startswith("--dir="):
            ingest = Path(os.path.expanduser(arg.split("=", 1)[1]))
    manifest_path = ingest / "manifest.json"
    if not manifest_path.is_file():
        raise SystemExit(f"Missing {manifest_path}")
    manifest = json.loads(manifest_path.read_text())
    keys = [item["key"] for item in manifest["items"]]
    print(f"keys={len(keys)} dir={ingest}", flush=True)
    token = load_token()
    upload_kind("thumbs", token, BUCKET_THUMBS, ingest / "thumbs", keys)
    upload_kind("full", token, BUCKET_FULL, ingest / "full", keys)
    print("All uploads complete.", flush=True)


if __name__ == "__main__":
    main()
