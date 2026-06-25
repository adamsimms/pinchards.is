<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/partials/microsite.php';

pinchard_microsite_head('53 Trees', [
	'body_attr' => 'id="page-top"',
	'google_fonts' => true,
	'font_awesome' => true,
]);
?>
    <nav id="mainNav" class="navbar navbar-default fixed-top">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="../gallery.php" class="link-to-gallery nav_cloudberry" aria-label="Browse photo gallery"></a>
            </div>
            <div class="nav-bar-center">
                <a href="#" class="title-brand">53 Trees</a>
            </div>
            <div class="nav-bar-end">
                <a class="nav_info" href="../info.php" aria-label="About this project"></a>
            </div>
        </div>
    </nav>

    <iframe src="https://www.google.com/maps/d/u/0/embed?mid=19NfRJjMQjtei3GXok6oK9WOqnsw" width="100%" height="100%" style="border:0" allowfullscreen title="53 Trees map"></iframe>

<?php pinchard_microsite_scripts_footer(); ?>
