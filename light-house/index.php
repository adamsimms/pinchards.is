<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/partials/microsite.php';

$pageTitle = 'Light House';
$description = 'Light House references the floating house in historical photographs of resettlement — a lighted beacon for vessels that never arrived.';
$canonical = pinchard_absolute_url('/light-house/');
$ogImage = 'https://i.vimeocdn.com/video/1032707037-a174af562315f0f4cfbdab65af3ff8a77e57e9845f4ce20abb42f1c1f7628629-d_1280x720?region=us';
$vimeoSrc = 'https://player.vimeo.com/video/499014653?background=1&autoplay=1&loop=1&muted=1&title=0&byline=0&portrait=0';

pinchard_microsite_head($pageTitle, [
	'body_attr' => 'id="page-top" class="light-house-page"',
	'description' => $description,
	'canonical_url' => $canonical,
	'og_image' => $ogImage,
	'og_type' => 'video.other',
	'json_ld' => [
		[
			'@type' => 'WebPage',
			'name' => $pageTitle,
			'description' => $description,
			'url' => $canonical,
			'isPartOf' => [
				'@type' => 'WebSite',
				'name' => "Pinchard's Island",
				'url' => pinchard_absolute_url('/'),
			],
		],
	],
]);
?>
    <nav id="mainNav" class="navbar navbar-default fixed-top" aria-label="Site">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="../index.php" class="title-brand title-brand--mark-only" aria-label="Cloudberry home">
                    <span class="title-brand-mark" aria-hidden="true"></span>
                </a>
            </div>
            <div class="nav-bar-center">
                <a href="#" class="title-brand">
                    <span class="title-brand-text">Light House</span>
                </a>
            </div>
            <div class="nav-bar-end">
                <a href="../gallery.php" class="link-to-gallery nav_gallery" aria-label="Browse photo gallery"></a>
                <a class="nav_info" href="../info.php" aria-label="About Cloudberry"></a>
            </div>
        </div>
    </nav>

    <h1 class="visually-hidden"><?= pinchard_h($pageTitle) ?></h1>
    <p class="visually-hidden"><?= pinchard_h($description) ?></p>
    <div class="light-house-shell">
        <div class="light-house-viewport">
            <iframe
                src="<?= pinchard_h($vimeoSrc) ?>"
                allow="autoplay; fullscreen; picture-in-picture"
                allowfullscreen
                title="Light House video"
            ></iframe>
        </div>
    </div>

<?php pinchard_microsite_scripts_footer(); ?>
