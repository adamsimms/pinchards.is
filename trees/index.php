<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/partials/microsite.php';

pinchard_microsite_head('53 Trees', [
	'body_attr' => 'id="page-top"',
	'google_fonts' => true,
	'font_awesome' => true,
]);
?>
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <a href="../gallery.php" class="link-to-gallery nav_cloudberry"></a>
        <a class="nav_info" href="../info.php"></a>
        <div class="title">
            <a href="#">53 Trees</a>
        </div>
    </nav>

    <iframe src="https://www.google.com/maps/d/u/0/embed?mid=19NfRJjMQjtei3GXok6oK9WOqnsw" width="100%" height="100%" style="border:0" allowfullscreen title="53 Trees map"></iframe>

<?php pinchard_microsite_scripts_footer(); ?>
