<?php
/*
 * Shared page header.
 * Set before including: $pageTitle (string), $active (home|games|about|contact|orders|admin|login|register|'').
 * Optional: $pageDescription, $bodyClass, $bare (true hides the site navigation, used by Entrance).
 */
$pageTitle       = $pageTitle ?? 'Novaspark Games';
$active          = $active ?? '';
$bodyClass       = $bodyClass ?? '';
$bare            = $bare ?? false;
$pageDescription = $pageDescription ?? 'Novaspark Games is an independent studio making games with tight stories and systems that reward curiosity.';
$user            = current_user();

$primaryLinks = [
    'home'    => ['index.php', 'Home'],
    'games'   => ['games.php', 'Games'],
    'about'   => ['about.php', 'About'],
    'contact' => ['contact.php', 'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle === 'Novaspark Games' ? $pageTitle : $pageTitle . ' · Novaspark Games') ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="theme-color" content="#071409">
  <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap">
  <link rel="stylesheet" href="<?= e(asset('css/tokens.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/layout.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/components.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/utilities.css')) ?>">
  <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<?php if (!$bare): ?>
<header class="site-header" data-site-header>
  <div class="site-header__inner container">
    <a class="logo" href="<?= e(url('index.php')) ?>"<?= $active === 'home' ? ' aria-label="Novaspark Games, home"' : '' ?>>Novaspark Games</a>

    <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="site-nav">
      <?= icon('menu', 'nav-toggle__open') ?><?= icon('close', 'nav-toggle__close') ?>
      <span class="visually-hidden">Menu</span>
    </button>

    <nav class="site-nav" id="site-nav" aria-label="Main">
      <ul class="site-nav__primary" role="list">
        <?php foreach ($primaryLinks as $key => [$href, $label]): ?>
          <li><a href="<?= e(url($href)) ?>" <?= nav_attrs($key, $active, 'nav-link') ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>

      <div class="site-nav__account">
        <?php if ($user): ?>
          <a href="<?= e(url('my-orders.php')) ?>" <?= nav_attrs('orders', $active, 'nav-link') ?>>My orders</a>
          <?php if ($user['role'] === 'admin'): ?>
            <a href="<?= e(url('admin/index.php')) ?>" <?= nav_attrs('admin', $active, 'nav-link nav-link--admin') ?>>Admin</a>
          <?php endif; ?>
          <div class="account-menu" data-account-menu>
            <button type="button" class="account-menu__button" aria-expanded="false" aria-controls="account-menu-panel" data-account-toggle>
              <?= icon('user') ?><span class="account-menu__name"><span class="visually-hidden">Account: </span><?= e($user['username']) ?></span><?= icon('chevron', 'account-menu__chevron') ?>
            </button>
            <div class="account-menu__panel" id="account-menu-panel">
              <p class="account-menu__who">Logged in as <strong><?= e($user['username']) ?></strong></p>
              <form method="post" action="<?= e(url('logout.php')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--ghost account-menu__logout">Log out</button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= e(url('login.php')) ?>" <?= nav_attrs('login', $active, 'nav-link') ?>>Log in</a>
          <a href="<?= e(url('register.php')) ?>" class="btn btn--secondary btn--compact<?= $active === 'register' ? ' is-current' : '' ?>"<?= $active === 'register' ? ' aria-current="page"' : '' ?>>Create account</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>
<?php endif; ?>
<main id="main" tabindex="-1">
<?php
$globalFlash = [];
if (flash('account_created')) $globalFlash[] = alert('success', "Account created. You're logged in as " . ($user['username'] ?? '') . '.');
if (flash('logged_out'))      $globalFlash[] = alert('success', "You've logged out. See you soon.");
if ($globalFlash): ?>
  <div class="container flash-area"><?= implode('', $globalFlash) ?></div>
<?php endif; ?>
