<?php
/* Useful Links: resources used to build the site and the Harvard reference list. */
require __DIR__ . '/includes/helpers.php';

$groups = [
    ['Development resources', 'HTML, CSS, JavaScript and PHP', [
        ['MDN Web Docs', 'https://developer.mozilla.org', 'developer.mozilla.org', 'HTML, CSS and JavaScript documentation used for syntax, accessibility attributes and browser support throughout development.'],
        ['W3Schools CSS Tutorial', 'https://www.w3schools.com/css/', 'w3schools.com/css', 'Quick reference for selectors, Flexbox, Grid and custom properties used in the responsive layouts.'],
        ['JavaScript.info', 'https://javascript.info', 'javascript.info', 'Modern JavaScript tutorials used for DOM updates, event listeners and the live game filter.'],
        ['PHP Manual', 'https://www.php.net/manual/en/', 'php.net/manual', 'Official PHP documentation for form handling, sessions, password hashing and database access.'],
        ['CSS-Tricks Flexbox Guide', 'https://css-tricks.com/snippets/css/a-guide-to-flexbox/', 'css-tricks.com', 'Visual guide to Flexbox used to structure headers, cards and form rows.'],
        ['W3C Markup Validator', 'https://validator.w3.org', 'validator.w3.org', 'Used to check every page is well-formed and follows current web standards.'],
        ['WebAIM Contrast Checker', 'https://webaim.org/resources/contrastchecker/', 'webaim.org', 'Used to confirm every text and background pair meets WCAG 2.1 AA contrast.'],
    ]],
    ['Design inspiration', 'Gaming sites and UI references', [
        ['Steam', 'https://store.steampowered.com', 'store.steampowered.com', 'Studied for dark-theme card layouts, hover states and genre filtering in a large game library.'],
        ['IGN', 'https://www.ign.com', 'ign.com', 'Referenced for article cards, news layouts and responsive navigation in a gaming context.'],
        ['Awwwards', 'https://www.awwwards.com', 'awwwards.com', 'Award-winning sites consulted for type pairings, hero composition and use of gradients.'],
        ['Coolors', 'https://coolors.co', 'coolors.co', 'Used to build and test the forest green, mint and amber palette before it became design tokens.'],
    ]],
    ['Media and assets', 'Images, icons and fonts', [
        ['Google Fonts', 'https://fonts.google.com', 'fonts.google.com', 'Source of Fraunces, Inter and JetBrains Mono, used under the SIL Open Font Licence.'],
        ['Unsplash', 'https://unsplash.com', 'unsplash.com', 'Photography referenced for the mood of the hero and game artwork, now redrawn as local illustrations.'],
        ['RAWG Video Games Database', 'https://rawg.io/apidocs', 'rawg.io', 'Game metadata reference used when planning genres, platforms and release information.'],
        ['Favicon.io', 'https://favicon.io', 'favicon.io', 'Used while designing the site icon shown in browser tabs.'],
    ]],
];

$accessed = '19 October 2026';
$references = [
    ['Mozilla Foundation (2024)', 'MDN Web Docs', 'https://developer.mozilla.org'],
    ['W3Schools (2024)', 'CSS Tutorial', 'https://www.w3schools.com/css/'],
    ['Kantor, I. (ed.) (2024)', 'The Modern JavaScript Tutorial', 'https://javascript.info'],
    ['The PHP Group (2024)', 'PHP: Hypertext Preprocessor Manual', 'https://www.php.net/manual/en/'],
    ["Coyier, C. (2022) 'A Complete Guide to Flexbox',", 'CSS-Tricks', 'https://css-tricks.com/snippets/css/a-guide-to-flexbox/'],
    ['World Wide Web Consortium (W3C) (2024)', 'Markup Validation Service', 'https://validator.w3.org'],
    ['WebAIM (2024)', 'Contrast Checker', 'https://webaim.org/resources/contrastchecker/'],
    ['World Wide Web Consortium (W3C) (2018)', 'Web Content Accessibility Guidelines (WCAG) 2.1', 'https://www.w3.org/TR/WCAG21/'],
    ['Valve Corporation (2024)', 'Steam Store', 'https://store.steampowered.com'],
    ['Ziff Davis (2024)', 'IGN: Game Reviews, News, Videos and Wikis', 'https://www.ign.com'],
    ['Awwwards (2024)', 'Website Awards: Best Web Design Trends', 'https://www.awwwards.com'],
    ['Coolors (2024)', 'Coolors: The Super Fast Color Palettes Generator', 'https://coolors.co'],
    ['Google (2024)', 'Google Fonts', 'https://fonts.google.com'],
    ['Unsplash (2024)', 'Beautiful Free Images and Pictures', 'https://unsplash.com'],
    ['RAWG (2024)', 'RAWG Video Games Database API', 'https://rawg.io/apidocs'],
    ['Favicon.io (2024)', 'Favicon Generator', 'https://favicon.io'],
];

$pageTitle = 'Useful Links';
$active = '';
$pageDescription = 'Resources and Harvard references used to research, design and build the Novaspark Games website.';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <header class="page-head">
    <h1 class="h1">Useful links</h1>
    <p class="lead">The resources used to research, design and build this website, listed for academic and professional transparency.</p>
  </header>

  <?php foreach ($groups as $i => [$title, $eyebrow, $items]): ?>
    <section class="section" aria-labelledby="group-<?= $i ?>">
      <?= section_header($title, $eyebrow, 'group-' . $i) ?>
      <ul class="card-grid card-grid--3" role="list">
        <?php foreach ($items as [$name, $href, $domain, $text]): ?>
          <li class="info-card resource-card">
            <a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"><?= e($name) ?><?= icon('external') ?><span class="visually-hidden"> (opens in a new tab)</span></a>
            <p class="mono"><?= e($domain) ?></p>
            <p><?= e($text) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endforeach; ?>

  <section class="section" aria-labelledby="refs-title">
    <?= section_header('Harvard reference list', 'Academic references', 'refs-title') ?>
    <ol class="references">
      <?php foreach ($references as [$author, $title, $href]): ?>
        <li><?= e($author) ?> <cite><?= e($title) ?></cite>. Available at: <a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"><?= e($href) ?><span class="visually-hidden"> (opens in a new tab)</span></a> (Accessed: <?= e($accessed) ?>).</li>
      <?php endforeach; ?>
    </ol>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
