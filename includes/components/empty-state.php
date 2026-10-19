<?php
/* Empty state: heading, one sentence, one action. */
function empty_state(string $heading, string $text, string $actionLabel = '', string $actionHref = '', bool $isButton = false): string
{
    $action = '';
    if ($actionLabel !== '') {
        $action = $isButton
            ? '<button type="button" class="btn btn--secondary" data-clear-filters>' . e($actionLabel) . '</button>'
            : '<a class="btn btn--secondary" href="' . e($actionHref) . '">' . e($actionLabel) . '</a>';
    }
    return '<div class="empty-state"><h2 class="empty-state__title">' . e($heading) . '</h2>'
        . '<p>' . e($text) . '</p>' . $action . '</div>';
}

/* Section header: optional eyebrow with hairlines, then the h2. */
function section_header(string $title, string $eyebrow = '', string $id = '', bool $centred = false): string
{
    $html = '<header class="section-header' . ($centred ? ' section-header--centred' : '') . '">';
    if ($eyebrow !== '') {
        $html .= '<p class="eyebrow"><span>' . e($eyebrow) . '</span></p>';
    }
    $html .= '<h2 class="h2"' . ($id ? ' id="' . e($id) . '"' : '') . '>' . e($title) . '</h2></header>';
    return $html;
}
