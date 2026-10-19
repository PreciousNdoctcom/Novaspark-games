<?php
/* Entrance: author credit and academic disclaimer. */
require __DIR__ . '/includes/helpers.php';

$pageTitle = 'Welcome';
$active = '';
$bare = true;
require __DIR__ . '/includes/header.php';
?>
<div class="entrance">
  <div class="entrance__inner reveal">
    <h1 class="display">Novaspark Games</h1>
    <div class="entrance__credit">
      <p class="lead">Created by Precious Nwachukwu</p>
      <p class="mono muted">Student ID 250733064</p>
    </div>
    <?= alert('info', 'This website is part of an academic assignment and is not a commercial site offering merchandise for sale. No payments are taken.', ['title' => 'Academic disclaimer']) ?>
    <a class="btn btn--primary" href="<?= e(url('index.php')) ?>" data-fade-link>Enter the website</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
