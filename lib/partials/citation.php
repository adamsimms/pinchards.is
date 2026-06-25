<?php

declare(strict_types=1);

/**
 * Copy-friendly citation block for researchers.
 *
 * @param array{
 *   text: string,
 *   label?: string,
 *   hint?: string,
 *   class?: string,
 * } $options
 */
function pinchard_citation_block(array $options): void
{
	$text = $options['text'];
	$label = $options['label'] ?? 'Suggested citation';
	$hint = $options['hint'] ?? 'Update the access date if you retrieved this material on a different day.';
	$class = $options['class'] ?? '';
	$blockClass = 'citation-block' . ($class !== '' ? ' ' . $class : '');
	?>
<div class="<?= pinchard_h($blockClass) ?>">
    <div class="citation-block-header">
        <span class="citation-block-label"><?= pinchard_h($label) ?></span>
        <button type="button" class="citation-copy-btn" data-citation="<?= pinchard_h($text) ?>" aria-label="Copy citation to clipboard">Copy</button>
    </div>
    <p class="citation-block-text" tabindex="0"><?= pinchard_h($text) ?></p>
<?php if ($hint !== ''): ?>
    <p class="citation-block-hint"><?= pinchard_h($hint) ?></p>
<?php endif; ?>
</div>
<?php
}
