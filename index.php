<?php
/* Home: what Novaspark is, featured games from the database, news and newsletter. */
require __DIR__ . '/includes/helpers.php';

/* ----- Newsletter sign-up (post, redirect, get) ----- */
$nlErrors = [];
$nlValues = ['nl_email' => '', 'nl_name' => ''];
if (is_post() && post('action') === 'subscribe') {
    $nlValues = ['nl_email' => post('nl_email'), 'nl_name' => post('nl_name')];
    if (!csrf_check()) {
        $nlErrors['form'] = "This form expired. We've kept what you typed; press Subscribe again.";
    } elseif ($nlValues['nl_email'] === '') {
        $nlErrors['nl_email'] = 'Enter your email.';
    } elseif (!filter_var($nlValues['nl_email'], FILTER_VALIDATE_EMAIL) || strlen($nlValues['nl_email']) > 100) {
        $nlErrors['nl_email'] = 'Enter an email like name@example.com.';
    } elseif (mb_strlen($nlValues['nl_name']) > 100) {
        $nlErrors['nl_name'] = 'Use 100 characters or fewer for your name.';
    }
    if (!$nlErrors) {
        try {
            $exists = q('SELECT id FROM newsletter WHERE email = ?', [$nlValues['nl_email']])->fetch();
            if (!$exists) {
                q('INSERT INTO newsletter (email, name) VALUES (?, ?)', [$nlValues['nl_email'], $nlValues['nl_name'] !== '' ? $nlValues['nl_name'] : null]);
            }
            flash('newsletter', $nlValues['nl_email']);
            redirect('index.php#newsletter');
        } catch (PDOException $e) {
            log_error($e);
            $nlErrors['form'] = MSG_SERVER_ERROR;
        }
    }
}
$nlSuccess = flash('newsletter');

/* ----- Featured games: three newest available, then one coming soon ----- */
$featured = [];
$dbError = false;
try {
    $featured = q("SELECT * FROM games WHERE status = 'available' ORDER BY release_date DESC, id DESC LIMIT 3")->fetchAll();
    $soon = q("SELECT * FROM games WHERE status = 'coming_soon' ORDER BY release_date ASC LIMIT 1")->fetch();
    if ($soon) {
        $featured[] = $soon;
    }
} catch (PDOException $e) {
    log_error($e);
    $dbError = true;
}

$news = [
    ['date' => '2026-10-14', 'title' => 'Void Protocol earns BAFTA recognition for its branching narrative system',
     'body' => "Void Protocol has received a BAFTA nomination for Best Narrative. The recognition highlights our branching dialogue engine, which lets player choices reshape the world in ways that persist across all three acts. It's a milestone for the studio and for independent development as a whole."],
    ['date' => '2026-10-06', 'title' => 'Nebula Drift has a release date: 26 February 2027',
     'body' => 'After two years in development, Nebula Drift launches on 26 February 2027 on PC and Mobile at the same time. It features over 40 handcrafted circuits set across asteroid belts, gas giant atmospheres and collapsing star systems.'],
    ['date' => '2026-09-22', 'title' => 'Novaspark is growing: 10 new developers have joined the team',
     'body' => "Ten new developers have joined the studio, bringing expertise in procedural generation, AI behaviour and multiplayer systems. The expanded team will work on two unannounced projects alongside continued support for our existing games. We can't wait to share what's next."],
    ['date' => '2026-09-01', 'title' => 'Iron Circuit open beta is live',
     'body' => 'The Iron Circuit open beta is available now. Play the first two campaigns, try the full mech customisation suite and climb the online leaderboard. We are collecting feedback before the full launch, so tell us what you think through the Contact page.'],
];

$pageTitle = 'Novaspark Games';
$active = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero" aria-labelledby="hero-title">
  <div class="container">
    <div class="hero__content reveal">
      <p class="label">Independent games studio</p>
      <h1 class="display" id="hero-title">Worlds that pull you in</h1>
      <p class="lead">We're an independent studio making games with tight stories and systems that reward curiosity. Find your next obsession.</p>
      <div class="hero__actions">
        <a class="btn btn--primary" href="<?= e(url('games.php')) ?>">Browse games</a>
        <a class="btn btn--secondary" href="<?= e(url('about.php')) ?>">Meet the studio</a>
      </div>
    </div>
  </div>
</section>

<div class="container">
  <section class="section" aria-labelledby="featured-title">
    <?= section_header('New and upcoming', 'Featured', 'featured-title', true) ?>
    <?php if ($dbError): ?>
      <?= alert('error', MSG_SERVER_ERROR) ?>
    <?php elseif (!$featured): ?>
      <?= empty_state('No games to show yet', 'New games will appear here as soon as they are added.', 'Browse games', url('games.php')) ?>
    <?php else: ?>
      <ul class="game-grid game-grid--4" role="list">
        <?php foreach ($featured as $game): ?>
          <li><?= game_card($game) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="section-more">
      <a class="link-arrow" href="<?= e(url('games.php')) ?>">See all games <?= icon('arrow') ?></a>
    </div>
  </section>

  <section class="section" aria-labelledby="pillars-title">
    <?= section_header('About Novaspark', 'The studio', 'pillars-title', true) ?>
    <div class="card-grid card-grid--3">
      <article class="info-card">
        <h3 class="info-card__title">Who we are</h3>
        <p>An independent studio founded by a small team with a big ambition: to make games that matter. We care about tight storytelling, satisfying mechanics and worlds that reward curiosity.</p>
      </article>
      <article class="info-card">
        <h3 class="info-card__title">Our vision</h3>
        <p>Every game starts with a question: what would make a player feel surprised, moved or proud? We design backwards from that feeling and build systems that deliver it every time.</p>
      </article>
      <article class="info-card">
        <h3 class="info-card__title">Our technology</h3>
        <p>We ship polished titles on PC, console and mobile using our own engine and industry tools. Accessibility and performance are built into the pipeline from day one, not added at the end.</p>
      </article>
    </div>
  </section>

  <section class="section" aria-labelledby="news-title">
    <?= section_header('Latest news', 'Studio updates', 'news-title', true) ?>
    <div class="news" data-news>
      <?php foreach ($news as $i => $item): $id = 'news-' . ($i + 1); ?>
        <div class="news__item">
          <h3 class="news__heading">
            <button type="button" class="news__button" id="<?= $id ?>-btn" aria-expanded="true" aria-controls="<?= $id ?>">
              <span class="news__date"><time datetime="<?= e($item['date']) ?>"><?= e(fmt_date($item['date'])) ?></time></span>
              <span class="news__title"><?= e($item['title']) ?></span>
              <?= icon('chevron') ?>
            </button>
          </h3>
          <div class="news__panel" id="<?= $id ?>" role="region" aria-labelledby="<?= $id ?>-btn" aria-live="polite">
            <p class="news__date"><time datetime="<?= e($item['date']) ?>"><?= e(fmt_date($item['date'])) ?></time></p>
            <p class="news__panel-title"><?= e($item['title']) ?></p>
            <p><?= e($item['body']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section" id="newsletter" aria-labelledby="newsletter-title">
    <div class="newsletter">
      <div class="stack">
        <p class="label">Newsletter</p>
        <h2 class="h2" id="newsletter-title">Stay ahead</h2>
        <p class="muted">News, early access invites and studio updates. No spam, unsubscribe any time.</p>
      </div>
      <form class="newsletter__form" method="post" action="<?= e(url('index.php')) ?>#newsletter" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="subscribe">
        <?php if ($nlSuccess || isset($nlErrors['form'])): ?>
          <div class="alerts">
            <?php if ($nlSuccess): ?>
              <?= alert('success', "You're on the list. We'll email " . $nlSuccess . ' with news and early access invites.') ?>
            <?php else: ?>
              <?= alert('error', $nlErrors['form']) ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?= field('nl_email', 'Email', ['type' => 'email', 'value' => $nlValues['nl_email'], 'error' => $nlErrors['nl_email'] ?? '', 'autocomplete' => 'email', 'required' => true, 'attrs' => 'data-label="email" maxlength="100"']) ?>
        <?= field('nl_name', 'Name', ['value' => $nlValues['nl_name'], 'error' => $nlErrors['nl_name'] ?? '', 'autocomplete' => 'name', 'optional' => true, 'attrs' => 'maxlength="100"']) ?>
        <button type="submit" class="btn btn--primary">Subscribe</button>
      </form>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
