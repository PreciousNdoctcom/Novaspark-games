<?php
/* Log in, then return to ?next=. */
require __DIR__ . '/includes/helpers.php';

$next = safe_next($_POST['next'] ?? $_GET['next'] ?? '', 'games.php');
if (current_user()) {
    redirect($next);
}

$values = ['username' => ''];
$errors = [];
$formError = '';

if (is_post()) {
    $values['username'] = post('username');
    $password = (string) ($_POST['password'] ?? '');
    if (!csrf_check()) {
        $formError = "This form expired. We've kept what you typed; press Log in again.";
    } else {
        if ($values['username'] === '') $errors['username'] = 'Enter your username.';
        if ($password === '')           $errors['password'] = 'Enter your password.';
    }
    if (!$errors && $formError === '') {
        try {
            $user = q('SELECT id, username, password, email, role FROM users WHERE username = ?', [$values['username']])->fetch();
            if ($user && password_verify($password, $user['password'])) {
                log_in($user);
                redirect($next);
            }
            $formError = "That username and password don't match. Try again.";
        } catch (PDOException $e) {
            log_error($e);
            $formError = MSG_SERVER_ERROR;
        }
    }
}

/* Login gate: name the game when the user came from Buy now. */
$gateGame = null;
if (preg_match('#^checkout\.php\?game_id=(\d+)#', $next, $m)) {
    try {
        $gateGame = find_game((int) $m[1]);
    } catch (PDOException $e) {
        log_error($e);
    }
}

$pageTitle = 'Log in';
$active = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="container auth">
  <div class="auth__card stack-lg">
    <header class="stack">
      <h1 class="h1">Log in</h1>
      <p class="muted">Welcome back. Log in to buy games and see your orders.</p>
    </header>

    <?php if ($gateGame && !is_post()): ?>
      <?= alert('info', 'Log in or create an account to buy ' . $gateGame['title'] . ". We'll bring you straight back.") ?>
    <?php endif; ?>

    <form class="form" method="post" action="<?= e(url('login.php')) ?>" data-validate>
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div data-error-summary>
        <?php if ($formError): ?><?= alert('error', $formError) ?><?php endif; ?>
      </div>
      <?= field('username', 'Username', ['value' => $values['username'], 'error' => $errors['username'] ?? '', 'autocomplete' => 'username', 'required' => true, 'attrs' => 'autocapitalize="none" spellcheck="false"']) ?>
      <?= field('password', 'Password', ['type' => 'password', 'error' => $errors['password'] ?? '', 'autocomplete' => 'current-password', 'required' => true]) ?>
      <button type="submit" class="btn btn--primary btn--block" data-loading-text="Logging in…">Log in</button>
    </form>

    <details class="small">
      <summary><span class="link-like">Forgot password?</span></summary>
      <p class="muted mt-2">Password resets aren't automated in this build. <a href="<?= e(url('contact.php')) ?>">Contact us</a> with your username and we'll reset it for you.</p>
    </details>

    <p class="form-divider">or</p>
    <p class="text-center">New here? <a href="<?= e(url('register.php?next=' . rawurlencode($next))) ?>">Create account</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
