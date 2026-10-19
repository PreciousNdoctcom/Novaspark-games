<?php
/*
 * Game card. The title link is stretched over the whole card; the Buy now
 * button sits above it so both stay clickable.
 */
function game_cover(array $game, string $class, int $w = 640, int $h = 360, bool $lazy = true): string
{
    $src = trim((string) ($game['image_url'] ?? ''));
    if ($src === '') {
        return '<div class="' . e($class) . ' cover-fallback"><span>' . e($game['title']) . '</span></div>';
    }
    return '<img class="' . e($class) . '" src="' . e(img_src($src)) . '" alt="Cover art for ' . e($game['title']) . '"'
        . ' width="' . $w . '" height="' . $h . '"' . ($lazy ? ' loading="lazy" decoding="async"' : '')
        . ' data-fallback="' . e($game['title']) . '">';
}

function game_card(array $game, string $headingLevel = 'h3'): string
{
    $soon = $game['status'] === 'coming_soon';
    $link = url('game.php?id=' . (int) $game['id']);
    $html = '<article class="game-card' . ($soon ? ' game-card--soon' : '') . '">';
    $html .= '<div class="game-card__media">' . game_cover($game, 'game-card__img');
    if ($game['status'] !== 'available') {
        $html .= '<div class="game-card__badge">' . status_badge($game['status']) . '</div>';
    }
    $html .= '</div><div class="game-card__body">';
    $html .= '<p class="game-card__meta">' . genre_tag($game['genre']) . '<span class="mono">' . (int) $game['release_year'] . '</span></p>';
    $html .= '<' . $headingLevel . ' class="game-card__title"><a class="game-card__link" href="' . e($link) . '">' . e($game['title']) . '</a></' . $headingLevel . '>';
    $html .= '<p class="game-card__desc">' . e($game['description']) . '</p>';
    $html .= '<div class="game-card__footer">';
    if ($soon) {
        $html .= '<p class="game-card__release"><span class="visually-hidden">Release date: </span>'
            . ($game['release_date'] ? 'Out ' . e(fmt_date($game['release_date'])) : 'Coming soon') . '</p>';
    } else {
        $html .= '<p class="price">' . e(money($game['price'])) . '</p>';
        if (can_buy($game)) {
            $html .= '<a class="btn btn--primary btn--compact game-card__buy" href="' . e(url('checkout.php?game_id=' . (int) $game['id'])) . '">'
                . 'Buy now<span class="visually-hidden"> ' . e($game['title']) . '</span></a>';
        }
    }
    $html .= '</div></div></article>';
    return $html;
}

function game_grid(array $games): string
{
    $html = '<ul class="game-grid" role="list">';
    foreach ($games as $game) {
        $html .= '<li>' . game_card($game) . '</li>';
    }
    return $html . '</ul>';
}
