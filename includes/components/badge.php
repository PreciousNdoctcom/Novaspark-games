<?php
/* Status badge and genre tag. */
function status_badge(string $status): string
{
    $variants = ['available' => 'success', 'coming_soon' => 'amber', 'beta' => 'warning', 'hidden' => 'muted'];
    $label = GAME_STATUSES[$status] ?? ucfirst($status);
    return '<span class="badge badge--' . ($variants[$status] ?? 'muted') . '">' . e($label) . '</span>';
}

function role_badge(string $role): string
{
    return '<span class="badge badge--' . ($role === 'admin' ? 'amber' : 'muted') . '">' . e($role === 'admin' ? 'Admin' : 'Customer') . '</span>';
}

function genre_tag(string $genre): string
{
    return '<span class="tag">' . e($genre) . '</span>';
}
