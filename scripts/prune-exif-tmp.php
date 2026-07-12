<?php

declare(strict_types=1);

/**
 * Remove leftover EXIF temp JPEGs under images/photo/.cache/exif-tmp/.
 *
 * Full-resolution downloads are now deleted after EXIF extraction; this script
 * clears historical accumulation (or any files left behind by interrupted runs).
 *
 * Usage:
 *   php scripts/prune-exif-tmp.php           # remove files older than TTL (default 1h)
 *   php scripts/prune-exif-tmp.php --all     # remove every temp JPEG immediately
 */

require_once dirname(__DIR__) . '/lib/bootstrap.php';

$forceAll = in_array('--all', array_slice($argv, 1), true);
$result = pinchard_exif_tmp_prune($forceAll);
$mb = number_format($result['bytes'] / (1024 * 1024), 1);

fwrite(STDERR, ($forceAll ? 'Removed all' : 'Pruned stale') . " EXIF temp JPEGs: {$result['removed']} files, {$mb} MiB freed.\n");
