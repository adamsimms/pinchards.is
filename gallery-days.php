<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

header('Location: ' . pinchard_absolute_url('/gallery.php'), true, 301);
exit;
