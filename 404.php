<?php
/* Not found. Also included by other pages when a record doesn't exist. */
if (!defined('APP_ROOT')) {
    require __DIR__ . '/includes/helpers.php';
}
if (http_response_code() === 200) {
    http_response_code(404);
}
$pageTitle = 'Page not found';
$active = '';
$bodyClass = '';
require APP_ROOT . '/includes/header.php';
?>
<div class="container page">
  <div class="empty-state">
    <p class="label">Error 404</p>
    <h1 class="h1">Page not found</h1>
    <p>We can't find that page.</p>
    <div class="actions">
      <a class="btn btn--primary" href="<?= e(url('index.php')) ?>">Go to Home</a>
      <a class="btn btn--secondary" href="<?= e(url('games.php')) ?>">Browse games</a>
    </div>
  </div>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
