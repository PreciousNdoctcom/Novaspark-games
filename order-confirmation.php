<?php
/* Order confirmation (the GET after checkout's POST). Refreshing never places a second order. */
require __DIR__ . '/includes/helpers.php';

$user = require_login();
$id = (int) ($_GET['id'] ?? 0);

try {
    $order = q('SELECT o.*, g.title, g.image_url, g.genre FROM orders o JOIN games g ON g.id = o.game_id WHERE o.id = ? AND o.user_id = ?', [$id, $user['id']])->fetch();
} catch (PDOException $e) {
    log_error($e);
    $order = false;
}
if (!$order) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$justPlaced = flash('order_placed');
$mailSent = is_array($justPlaced) && (int) $justPlaced['id'] === $id ? (bool) $justPlaced['mail_sent'] : null;
$qty = (int) $order['quantity'];
$unit = $order['unit_price'] !== null ? $order['unit_price'] : $order['total_price'] / max(1, $qty);
$sentence = 'Thanks, ' . $user['username'] . '. Order ' . order_no($order['id']) . ' for ' . $qty . ' × ' . $order['title'] . ' (' . money($order['total_price']) . ') is confirmed.';
if ($mailSent === true) {
    $sentence .= ' A copy has been sent to ' . $order['contact_email'] . '.';
} elseif ($mailSent === false) {
    $sentence .= " We couldn't email a copy just now, but your order is saved and listed in My orders.";
}

$pageTitle = 'Order placed';
$active = 'orders';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <div class="split">
    <div class="confirm-hero">
      <div class="confirm-hero__icon"><?= icon('check') ?></div>
      <h1 class="h1">Order placed</h1>
      <p class="lead"><?= e($sentence) ?></p>
      <p class="summary__note"><?= icon('info') ?><span>No payment is taken. This is a simulated order for an academic project.</span></p>
      <div class="actions mt-4">
        <a class="btn btn--primary" href="<?= e(url('my-orders.php')) ?>">View my orders</a>
        <a class="btn btn--secondary" href="<?= e(url('games.php')) ?>">Keep browsing</a>
      </div>
    </div>

    <section class="summary" aria-labelledby="summary-title">
      <h2 class="label" id="summary-title">Order summary</h2>
      <div class="summary__game">
        <?= game_cover($order, 'summary__thumb', 224, 126, false) ?>
        <div>
          <p class="summary__title"><?= e($order['title']) ?></p>
          <p class="small muted"><?= e($order['genre']) ?></p>
        </div>
      </div>
      <dl>
        <div><dt>Order number</dt><dd class="mono"><?= e(order_no($order['id'])) ?></dd></div>
        <div><dt>Licences</dt><dd class="mono"><?= $qty ?></dd></div>
        <div><dt>Unit price</dt><dd class="mono"><?= e(money($unit)) ?></dd></div>
        <div><dt>Date</dt><dd class="mono"><?= e(fmt_date($order['order_date'])) ?>, <?= e(fmt_time($order['order_date'])) ?></dd></div>
      </dl>
      <div class="summary__divider"></div>
      <div class="summary__total">
        <strong>Total</strong>
        <span class="price price--total"><?= e(money($order['total_price'])) ?></span>
      </div>
    </section>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
