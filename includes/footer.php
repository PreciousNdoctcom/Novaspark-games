<?php
/* Shared page footer. Closes <main> opened in header.php. */
$user = current_user();
$bare = $bare ?? false;
?>
</main>
<?php if (!$bare): ?>
<footer class="site-footer">
  <div class="container">
    <div class="site-footer__grid">
      <div class="site-footer__brand">
        <p class="logo logo--footer">Novaspark Games</p>
        <p>An independent studio building worlds that pull you in, from the opening title screen to the final credits.</p>
      </div>
      <nav class="site-footer__links" aria-label="Footer">
        <p class="label">Explore</p>
        <ul role="list">
          <li><a href="<?= e(url('games.php')) ?>">Games</a></li>
          <li><a href="<?= e(url('about.php')) ?>">About</a></li>
          <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
          <li><a href="<?= e(url('links.php')) ?>">Useful Links</a></li>
          <li><a href="<?= e(url('entrance.php')) ?>">Entrance</a></li>
          <?php if ($user): ?>
            <li><a href="<?= e(url('my-orders.php')) ?>">My orders</a></li>
          <?php endif; ?>
          <?php if ($user && $user['role'] === 'admin'): ?>
            <li><a href="<?= e(url('admin/index.php')) ?>">Admin</a></li>
          <?php endif; ?>
        </ul>
      </nav>
      <div class="site-footer__contact">
        <p class="label">Get in touch</p>
        <p><a href="mailto:<?= e(config('PUBLIC_EMAIL')) ?>"><?= e(config('PUBLIC_EMAIL')) ?></a></p>
        <p>We reply within two working days.</p>
      </div>
    </div>
    <div class="site-footer__bottom">
      <p>Academic project by Precious Nwachukwu (ID 250733064). Not a commercial store; no payments are taken. <a href="<?= e(url('entrance.php')) ?>">Read the disclaimer</a></p>
      <p>&copy; 2026 Novaspark Games</p>
    </div>
  </div>
</footer>
<?php endif; ?>

<dialog class="dialog" id="confirm-dialog" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-body">
  <div class="dialog__inner">
    <h2 class="dialog__title" id="confirm-dialog-title">Are you sure?</h2>
    <p class="dialog__body" id="confirm-dialog-body"></p>
    <div class="dialog__actions">
      <button type="button" class="btn btn--ghost" data-dialog-cancel>Cancel</button>
      <button type="button" class="btn btn--danger" data-dialog-confirm>Delete</button>
    </div>
  </div>
</dialog>
</body>
</html>
