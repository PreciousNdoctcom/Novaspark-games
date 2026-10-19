<?php
/* About: origin story, values and the team. */
require __DIR__ . '/includes/helpers.php';

$team = [
    ['id' => 'pn', 'name' => 'Precious Nwachukwu', 'role' => 'Lead Developer and Founder',
     'bio' => "Precious founded Novaspark Games after six years shipping titles at two AAA studios. She leads engine architecture and core gameplay systems, and is the reason the studio's frame rate targets are non-negotiable."],
    ['id' => 'er', 'name' => 'Elena Rostova', 'role' => 'Art Director',
     'bio' => 'Elena brings a background in concept art and film production design to every Novaspark title. She sets the visual language of each project, from the first mood board to the final colour grade.'],
    ['id' => 'mt', 'name' => 'Marcus Thorne', 'role' => 'Audio Engineer',
     'bio' => "Marcus composes original scores and builds the full sound design pipeline. His adaptive audio system, which responds in real time to the player's state, is one of the studio's most praised technical achievements."],
];

$pageTitle = 'About';
$active = 'about';
$pageDescription = 'How Novaspark Games began, what drives the studio and the people behind it.';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <header class="page-head reveal">
    <p class="label">About Novaspark</p>
    <h1 class="h1">Our story</h1>
    <p class="lead">Novaspark Games began as a two-person project in a shared flat, built on strong coffee and a conviction that independent studios can compete with the biggest names in the industry, on one condition: boldness.</p>
  </header>

  <section class="section" aria-labelledby="values-title">
    <?= section_header('Core values', 'What drives us', 'values-title') ?>
    <div class="card-grid card-grid--3">
      <article class="info-card">
        <h3 class="info-card__title">Innovation first</h3>
        <p>We build things we haven't seen before. Our own engine lets us prototype fast and ship mechanics that feel fresh, not borrowed from last year's bestseller.</p>
      </article>
      <article class="info-card">
        <h3 class="info-card__title">Player-centric</h3>
        <p>Our playtesting is ruthless. If a feature doesn't make the game better for the person holding the controller, it doesn't ship.</p>
      </article>
      <article class="info-card">
        <h3 class="info-card__title">Artistic integrity</h3>
        <p>Every visual, every note and every line of dialogue goes through several rounds of creative review. We don't compromise on craft.</p>
      </article>
    </div>
  </section>

  <section class="section" aria-labelledby="team-title">
    <?= section_header('Meet the team', 'The people behind the games', 'team-title') ?>
    <ul class="team-grid" role="list">
      <?php foreach ($team as $m): ?>
        <li>
          <button type="button" class="team-card" data-dialog-open="bio-<?= e($m['id']) ?>" aria-haspopup="dialog">
            <img class="team-card__avatar" src="<?= e(asset('img/team/' . $m['id'] . '.svg')) ?>" alt="" width="96" height="96" loading="lazy">
            <span class="team-card__name"><?= e($m['name']) ?></span>
            <span class="team-card__role"><?= e($m['role']) ?></span>
            <span class="team-card__more">Read bio</span>
          </button>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php foreach ($team as $m): ?>
      <dialog class="dialog" id="bio-<?= e($m['id']) ?>" aria-labelledby="bio-<?= e($m['id']) ?>-name">
        <div class="dialog__inner bio">
          <button type="button" class="dialog__close" data-dialog-close><?= icon('close') ?>Close</button>
          <img class="bio__avatar" src="<?= e(asset('img/team/' . $m['id'] . '.svg')) ?>" alt="" width="128" height="128">
          <h2 class="dialog__title" id="bio-<?= e($m['id']) ?>-name"><?= e($m['name']) ?></h2>
          <p class="team-card__role"><?= e($m['role']) ?></p>
          <p><?= e($m['bio']) ?></p>
        </div>
      </dialog>
    <?php endforeach; ?>

    <noscript>
      <div class="card-grid card-grid--3 mt-6">
        <?php foreach ($team as $m): ?>
          <article class="info-card"><h3 class="info-card__title"><?= e($m['name']) ?></h3><p><?= e($m['bio']) ?></p></article>
        <?php endforeach; ?>
      </div>
    </noscript>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
