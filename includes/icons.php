<?php
/*
 * Inline SVG line icons. Decorative by default (aria-hidden); the text next
 * to each icon carries the meaning.
 */

function icon(string $name, string $class = ''): string
{
    $paths = [
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'alert'    => '<path d="M12 3.5l9.5 16.5h-19z"/><path d="M12 10v4.5"/><path d="M12 17.5v.01"/>',
        'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.5v.01"/>',
        'success'  => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.8 2.8L16.5 9.5"/>',
        'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
        'chevron'  => '<path d="M6 9l6 6 6-6"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'external' => '<path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'eye'      => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'minus'    => '<path d="M5 12h14"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'user'     => '<circle cx="12" cy="8.5" r="4"/><path d="M4 20.5c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'mail'     => '<rect x="3" y="5.5" width="18" height="13" rx="1"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'bag'      => '<path d="M5 8h14l-1 12H6z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
        'spark'    => '<path d="M12 3v5M12 16v5M3 12h5M16 12h5M6 6l3 3M15 15l3 3M18 6l-3 3M9 15l-3 3"/>',
    ];
    $body = $paths[$name] ?? $paths['info'];
    $cls = 'icon' . ($class !== '' ? ' ' . $class : '');
    return '<svg class="' . e($cls) . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
}
