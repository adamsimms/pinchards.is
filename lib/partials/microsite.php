<?php

declare(strict_types=1);

/**
 * Shared head/nav assets for PHP mini-sites under jam/, trees/, resettled/.
 *
 * @param array{
 *   body_attr?: string,
 *   google_fonts?: bool,
 *   font_awesome?: bool,
 *   extra_head?: string,
 * } $options
 */
function pinchard_microsite_head(string $title, array $options = []): void
{
	$bodyAttr = $options['body_attr'] ?? '';
	$googleFonts = $options['google_fonts'] ?? false;
	$fontAwesome = $options['font_awesome'] ?? false;
	$extraHead = $options['extra_head'] ?? '';
	$t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?= $t ?></title>
    <link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
<?php if ($fontAwesome): ?>
    <link href="../vendor/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css">
<?php endif; ?>
<?php if ($googleFonts): ?>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic" rel="stylesheet">
<?php endif; ?>
    <link href="../css/pinchard.css" rel="stylesheet">
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.js"></script>
    <![endif]-->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../favicon/manifest.json">
    <link rel="mask-icon" href="../favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="../favicon/favicon.ico">
    <meta name="msapplication-config" content="../favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
<?php
	if ($extraHead !== '') {
		echo $extraHead;
	}
?>
</head>
<body<?= $bodyAttr !== '' ? ' ' . $bodyAttr : '' ?>>
<?php
}

function pinchard_microsite_scripts_footer(): void
{
	?>
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.js"></script>
    <script src="../js/pinchard.js"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G1XKSQNT5M"></script>
    <script>
         window.dataLayer = window.dataLayer || [];
         function gtag(){dataLayer.push(arguments);}
         gtag('js', new Date());
         gtag('config', 'G-G1XKSQNT5M');
    </script>
</body>
</html>
<?php
}
