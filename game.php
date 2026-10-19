<?php
/* Game detail: decide and buy. */
require __DIR__ . '/includes/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
$game = null;
$related = [];
$relatedTitle = '';
try {
    $game = $id > 0 ? find_game($id) : null;
    if ($game) {
        $related = q("SELECT * FROM games WHERE genre = ? AND id <> ? AND status <> 'hidden' ORDER BY release_year DESC LIMIT 3", [$game['genre'], $id])->fetchAll();
        $relatedTitle = 'More in ' . $game['genre'];
        if (!$related) {
            $related = q("SELECT * FROM games WHERE id <> ? AND status IN ('available','beta') ORDER BY release_date DESC LIMIT 3", [$id])->fetchAll();
            $relatedTitle = 'You might also like';
        }
    }
} catch (PDOException $e) {
    log_error($e);
    $pageTitle = 'Game';
    $active = 'games';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container page">' . alert('error', MSG_SERVER_ERROR) . '</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

if (!$game) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$soon = $game['status'] === 'coming_soon';
$buyUrl = url('checkout.php?game_id=' . (int) $game['id']);

$pageTitle = $game['title'];
$active = 'games';
$pageDescription = $game['description'];
$bodyClass = (!$soon && can_buy($game)) ? 'has-buy-bar' : '';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <p class="mb-5"><a class="link-arrow" href="<?= e(url('games.php')) ?>">All games</a></p>

  <div class="split split--even">
    <div class="game-detail__media">
      <?= game_cover($game, 'game-detail__cover', 1600, 900, false) ?>
    </div>

    <div class="game-detail__info">
      <p class="game-card__meta"><?= genre_tag($game['genre']) ?><span class="mono"><?= (int) $game['release_year'] ?></span><?= $game['status'] !== 'available' ? status_badge($game['status']) : '' ?></p>
      <h1 class="h1"><?= e($game['title']) ?></h1>

      <?php if ($game['status'] === 'beta'): ?>
        <?= alert('warning', 'This game is in beta. Your licence includes the full game when it launches.') ?>
      <?php endif; ?>

      <div class="game-detail__buy">
        <?php if ($soon): ?>
          <div class="game-detail__price-row">
            <span class="label">Release date</span>
            <span class="price"><?= $game['release_date'] ? e(fmt_date($game['release_date'])) : 'To be announced' ?></span>
          </div>
          <p class="small muted">This game isn't available to buy yet. Check back on launch day.</p>
        <?php else: ?>
          <div class="game-detail__price-row">
            <span class="label">Price per licence</span>
            <span class="price price--total"><?= e(money($game['price'])) ?></span>
          </div>
          <?php if (can_buy($game)): ?>
            <a class="btn btn--primary" href="<?= e($buyUrl) ?>">Buy now</a>
          <?php endif; ?>
          <p class="summary__note"><?= icon('info') ?><span>No payment is taken. This is a simulated order for an academic project.</span></p>
        <?php endif; ?>
      </div>

      <div class="prose">
        <p><?= e($game['description']) ?></p>
      </div>

      <dl class="facts">
        <dt>Genre</dt><dd><?= e($game['genre']) ?></dd>
        <?php if ($game['platforms']): ?><dt>Platforms</dt><dd><?= e($game['platforms']) ?></dd><?php endif; ?>
        <dt>Released</dt><dd><?= $game['release_date'] ? e(fmt_date($game['release_date'])) : (int) $game['release_year'] ?></dd>
        <dt>Status</dt><dd><?= e(GAME_STATUSES[$game['status']] ?? '') ?></dd>
      </dl>
    </div>
  </div>

  <?php if ($related): ?>
    <section class="section" aria-labelledby="related-title">
      <?= section_header($relatedTitle, '', 'related-title') ?>
      <?= game_grid($related) ?>
    </section>
  <?php endif; ?>
</div>

<?php if (!$soon && can_buy($game)): ?>
  <div class="buy-bar" aria-label="Buy <?= e($game['title']) ?>" role="region">
    <span class="price"><?= e(money($game['price'])) ?></span>
    <a class="btn btn--primary" href="<?= e($buyUrl) ?>">Buy now</a>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
