<?php
/* One order on My orders. */
function order_row(array $o): string
{
    $licences = (int) $o['quantity'] === 1 ? '1 licence' : (int) $o['quantity'] . ' licences';
    return '<li class="order-row">'
        . '<div class="order-row__game">' . game_cover(['title' => $o['title'], 'image_url' => $o['image_url']], 'order-row__thumb', 160, 90)
        . '<div><p class="mono order-row__no">Order ' . e(order_no($o['id'])) . '</p>'
        . '<h2 class="order-row__title">' . e($o['title']) . '</h2>'
        . '<p class="order-row__meta">' . e($licences) . '</p></div></div>'
        . '<dl class="order-row__facts">'
        . '<div><dt>Date</dt><dd class="mono"><time datetime="' . e(date('c', strtotime($o['order_date']))) . '">' . e(fmt_date($o['order_date'])) . '</time></dd></div>'
        . '<div><dt>Total</dt><dd class="price">' . e(money($o['total_price'])) . '</dd></div>'
        . '</dl></li>';
}
