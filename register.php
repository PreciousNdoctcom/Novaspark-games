<?php
/* Create account, log in automatically and return to ?next=. */
require __DIR__ . '/includes/helpers.php';

$next = safe_next($_POST['next'] ?? $_GET['next'] ?? '', 'games.php');
if (current_user()) {
    redirect($next);
}

$values = ['username' => '', 'email' => ''];
$errors = [];
$formError = '';

if (is_post()) {
    $values['username'] = post('username');
    $values['email'] = post('email');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (!csrf_check()) {
        $formError = "This form expired. We've kept what you typed; press Create account again.";
    } else {
        if ($values['username'] === '') {
            $errors['username'] = 'Enter your username.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $values['username'])) {
            $errors['username'] = 'Use 3 to 50 letters, numbers, dots, dashes or underscores.';
        }
        if ($values['email'] === '') {
            $errors['email'] = 'Enter your email.';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($values['email']) > 100) {
            $errors['email'] = 'Enter an email like name@example.com.';
        }
        if ($password === '') {
            $errors['password'] = 'Enter your password.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'At least 8 characters.';
        }
        if ($confirm === '') {
            $errors['confirm_password'] = 'Enter your password again.';
        } elseif ($password !== $confirm) {
            $errors['confirm_password'] = "The passwords don't match.";
        }
    }

    if (!$errors && $formError === '') {
        try {
            if (q('SELECT id FROM users WHERE username = ?', [$values['username']])->fetch()) {
                $errors['username'] = 'That username is taken. Try another.';
            }
            if (q('SELECT id FROM users WHERE email = ?', [$values['email']])->fetch()) {
                $errors['email'] = "There's already an account with that email. Log in instead?";
            }
            if (!$errors) {
                q("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'customer')",
                  [$values['username'], password_hash($password, PASSWORD_DEFAULT), $values['email']]);
                log_in(['id' => (int) db()->lastInsertId(), 'username' => $values['username'], 'email' => $values['email'], 'role' => 'customer']);
                flash('account_created', true);
                redirect($next);
            }
        } catch (PDOException $e) {
            log_error($e);
            $formError = MSG_SERVER_ERROR;
        }
    }
}

$pageTitle = 'Create account';
$active = 'register';
require __DIR__ . '/includes/header.php';
?>
<div class="container auth">
  <div class="auth__card stack-lg">
    <header class="stack">
      <h1 class="h1">Create account</h1>
      <p class="muted">An account lets you place simulated orders and keep track of them.</p>
    </header>

    <form class="form" method="post" action="<?= e(url('register.php')) ?>" data-validate>
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div data-error-summary>
        <?php if ($formError): ?><?= alert('error', $formError) ?><?php endif; ?>
        <?= error_summary($errors) ?>
      </div>
      <?= field('username', 'Username', ['value' => $values['username'], 'error' => $errors['username'] ?? '', 'autocomplete' => 'username', 'required' => true, 'helper' => '3 to 50 letters or numbers. This is how you log in.', 'attrs' => 'autocapitalize="none" spellcheck="false" maxlength="50"']) ?>
      <?= field('email', 'Email', ['type' => 'email', 'value' => $values['email'], 'error' => $errors['email'] ?? '', 'autocomplete' => 'email', 'required' => true, 'attrs' => 'data-label="email" maxlength="100"']) ?>
      <?= field('password', 'Password', ['type' => 'password', 'error' => $errors['password'] ?? '', 'autocomplete' => 'new-password', 'required' => true, 'helper' => 'At least 8 characters.', 'attrs' => 'data-minlength="8"']) ?>
      <?= field('confirm_password', 'Confirm password', ['type' => 'password', 'error' => $errors['confirm_password'] ?? '', 'autocomplete' => 'new-password', 'required' => true, 'attrs' => 'data-match="password" data-msg-required="Enter your password again."']) ?>
      <ul class="checks" role="list" data-password-rules="password" data-password-confirm="confirm_password" aria-label="Password checks">
        <li data-rule="length"><?= icon('check') ?>At least 8 characters<span class="visually-hidden"> (not yet)</span></li>
        <li data-rule="match"><?= icon('check') ?>Passwords match<span class="visually-hidden"> (not yet)</span></li>
      </ul>
      <button type="submit" class="btn btn--primary btn--block" data-loading-text="Creating account…">Create account</button>
    </form>

    <p class="text-center">Already have an account? <a href="<?= e(url('login.php?next=' . rawurlencode($next))) ?>">Log in</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
