<?php

declare(strict_types=1);

/**
 * Legacy JSON endpoint for old gallery experiments (same-origin relative URL).
 * Current gallery.php does not use this. Returns an empty payload shape for any stray clients.
 */
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
	'array' => [],
	'validYearArray' => [],
	'validMonthArray' => [],
]);
