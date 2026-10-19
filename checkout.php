<?php
/* Checkout: confirm one game, choose licences and place a simulated order. */
require __DIR__ . '/includes/helpers.php';

$gameId = (int) ($_POST['game_id'] ?? $_GET['game_id'] ?? 0);
if ($gameId <= 0) {
    redirect('games.php');
}
$user = require_login();

const MAX_LICENCES = 10;

try {
    $game = find_game($gameId);
} catch (PDOException $e) {
    log_error($e);
    $game = null;
    $loadError = true;
}

if (!$game && empty($loadError)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$quantity = 1;
$email = $user['email'];
$errors = [];
$formError = '';

if ($game && is_post()) {
    $quantity = (int) post('quantity');
    $email = post('contact_email') !== '' ? post('contact_email') : $user['email'];

    if (!csrf_check()) {
        $formError = "This form expired. We've kept your choices; press Place order again.";
    } elseif (!can_buy($game)) {
        $formError = $game['title'] . " isn't available to buy yet.";
    } else {
        if ($quantity < 1 || $quantity > MAX_LICENCES) {
            $errors['quantity'] = 'Choose between 1 and 10 licences.';
            $quantity = max(1, min(MAX_LICENCES, $quantity));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            $errors['contact_email'] = 'Enter an email like name@example.com.';
        }
    }

    if (!$errors && $formError === '') {
        try {
            $unit = (float) $game['price'];
            $total = round($unit * $quantity, 2);
            q('INSERT INTO orders (user_id, game_id, quantity, unit_price, total_price, contact_email) VALUES (?, ?, ?, ?, ?, ?)',
              [$user['id'], $game['id'], $quantity, $unit, $total, $email]);
            $orderId = (int) db()->lastInsertId();

            $licences = $quantity === 1 ? '1 licence' : $quantity . ' licences';
            $body = "Hi {$user['username']},\n\n"
                  . "Thanks for your order with Novaspark Games.\n\n"
                  . 'Order number: ' . order_no($orderId) . "\n"
                  . "Game: {$game['title']}\n"
                  . "Licences: {$licences}\n"
                  . 'Unit price: ' . money($unit) . "\n"
                  . 'Total: ' . money($total) . "\n"
                  . 'Date: ' . date('j M Y, H:i') . "\n\n"
                  . "No payment is taken. This is a simulated order for an academic project.\n\n"
                  . "Novaspark Games\n";
            $sent = send_mail($email, 'Your Novaspark order ' . order_no($orderId), $body);
            send_mail(config('STUDIO_EMAIL'), 'New simulated order ' . order_no($orderId), "Customer: {$user['username']} <{$email}>\n\n" . $body);

            flash('order_placed', ['id' => $orderId, 'mail_sent' => $sent]);
            redirect('order-confirmation.php?id=' . $orderId);
        } catch (PDOException $e) {
            log_error($e);
            $formError = "We couldn't place your order. Nothing was charged. Try again, or contact us if it keeps happening.";
        }
    }
}

$pageTitle = 'Checkout';
$active = 'games';
require __DIR__ . '/includes/header.php';

if (!$game): ?>
  <div class="container page"><?= alert('error', MSG_SERVER_ERROR) ?></div>
<?php require __DIR__ . '/includes/footer.php'; exit; endif;

$total = (float) $game['price'] * $quantity;
$licencesLabel = $quantity === 1 ? '1 licence' : $quantity . ' licences';
?>
<div class="container page">
  <header class="page-head page-head--tight">
    <h1 class="h1">Checkout</h1>
    <p class="muted">No payment is taken. This is a simulated order for an academic project.</p>
  </header>

  <div data-error-summary>
    <?php if ($formError): ?><?= alert('error', $formError) ?><?php endif; ?>
    <?= error_summary($errors) ?>
  </div>

  <?php if (!can_buy($game)): ?>
    <?= alert('info', $game['title'] . " isn't available to buy yet." . ($game['release_date'] ? ' It launches on ' . fmt_date($game['release_date']) . '.' : '')) ?>
    <p class="mt-5"><a class="btn btn--secondary" href="<?= e(url('games.php')) ?>">Keep browsing</a></p>
  <?php else: ?>
  <form method="post" action="<?= e(url('checkout.php?game_id=' . (int) $game['id'])) ?>" data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="game_id" value="<?= (int) $game['id'] ?>">
    <div class="checkout">
      <section class="stack-lg" aria-labelledby="details-title">
        <h2 class="h2" id="details-title">Your details</h2>
        <dl class="stack">
          <div class="field-readonly">
            <dt>Name</dt>
            <dd><?= e($user['username']) ?></dd>
          </div>
          <div class="field-readonly" id="email-readonly">
            <dt>Email for this order</dt>
            <dd><?= e($email) ?></dd>
          </div>
        </dl>
        <div>
          <button type="button" class="btn btn--ghost" data-edit-toggle="email-readonly" aria-expanded="false" aria-controls="email-edit">Edit</button>
        </div>
        <div id="email-edit" data-edit-panel>
          <?= field('contact_email', 'Email for this order', ['type' => 'email', 'value' => $email, 'error' => $errors['contact_email'] ?? '', 'autocomplete' => 'email', 'helper' => "We'll send the confirmation here. Your account email doesn't change.", 'attrs' => 'maxlength="100"']) ?>
        </div>
        <?= alert('info', 'Licences are delivered as a simulated order record. You can see every order on My orders.') ?>
        <div class="checkout__submit-mobile">
          <button type="submit" class="btn btn--primary btn--block" data-loading-text="Placing order…">Place order</button>
        </div>
      </section>

      <aside class="checkout__summary summary" aria-labelledby="summary-title">
        <h2 class="label" id="summary-title">Order summary</h2>
        <div class="summary__game">
          <?= game_cover($game, 'summary__thumb', 224, 126, false) ?>
          <div>
            <p class="summary__title"><?= e($game['title']) ?></p>
            <p class="small muted"><?= e($game['genre']) ?> · <a href="<?= e(url('games.php')) ?>">Change game</a></p>
          </div>
        </div>
        <div class="summary__row">
          <span class="muted">Unit price</span>
          <span class="mono"><?= e(money($game['price'])) ?></span>
        </div>
        <div class="field">
          <div class="summary__row">
            <label class="field__label" for="quantity">Licences</label>
            <div class="stepper" data-stepper data-price="<?= e(number_format((float) $game['price'], 2, '.', '')) ?>">
              <button type="button" class="stepper__btn" data-step="-1" aria-label="Remove a licence"><?= icon('minus') ?></button>
              <input class="stepper__input" type="number" id="quantity" name="quantity" value="<?= (int) $quantity ?>" min="1" max="<?= MAX_LICENCES ?>" step="1" inputmode="numeric" required aria-describedby="quantity-help" data-msg-range="Choose between 1 and 10 licences.">
              <button type="button" class="stepper__btn" data-step="1" aria-label="Add a licence"><?= icon('plus') ?></button>
            </div>
          </div>
          <p class="field__helper" id="quantity-help">Up to 10 licences</p>
          <p class="field__error" id="quantity-error"<?= isset($errors['quantity']) ? '' : ' hidden' ?> data-field-error><?= icon('alert') ?><span><?= e($errors['quantity'] ?? '') ?></span></p>
        </div>
        <div class="summary__divider"></div>
        <div class="summary__total" aria-live="polite">
          <span><strong>Total</strong> <span class="small muted" data-qty-label><?= e($licencesLabel) ?></span></span>
          <span class="price price--total" data-total><?= e(money($total)) ?></span>
        </div>
        <p class="summary__note"><?= icon('info') ?><span>No payment is taken. This is a simulated order.</span></p>
        <div class="checkout__submit-desktop">
          <button type="submit" class="btn btn--primary btn--block" data-loading-text="Placing order…">Place order</button>
        </div>
      </aside>
    </div>
  </form>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
