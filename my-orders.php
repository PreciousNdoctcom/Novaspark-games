<?php
/* My orders: the signed-in customer's order history, newest first. */
require __DIR__ . '/includes/helpers.php';

$user = require_login();
$orders = [];
$dbError = false;
try {
    $orders = q('SELECT o.*, g.title, g.image_url FROM orders o JOIN games g ON g.id = o.game_id WHERE o.user_id = ? ORDER BY o.order_date DESC, o.id DESC', [$user['id']])->fetchAll();
} catch (PDOException $e) {
    log_error($e);
    $dbError = true;
}
$count = count($orders);
$spent = array_sum(array_map(fn($o) => (float) $o['total_price'], $orders));

$pageTitle = 'My orders';
$active = 'orders';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <header class="page-head page-head--tight">
    <h1 class="h1">My orders</h1>
    <?php if (!$dbError && $count): ?>
      <p class="mono muted"><?= $count === 1 ? '1 order' : $count . ' orders' ?> · Total (simulated) <span class="amber"><?= e(money($spent)) ?></span></p>
    <?php endif; ?>
  </header>

  <?php if ($dbError): ?>
    <?= alert('error', MSG_SERVER_ERROR) ?>
  <?php elseif (!$orders): ?>
    <?= empty_state('No orders yet', "When you buy a game it'll appear here.", 'Browse games', url('games.php')) ?>
  <?php else: ?>
    <ol class="order-list" role="list">
      <?php foreach ($orders as $o): ?>
        <?= order_row($o) ?>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
