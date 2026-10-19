<?php
/* Games: search, filter by genre and sort. Replaces products.php and search.php. */
require __DIR__ . '/includes/helpers.php';

$sorts = [
    'title'      => 'Title A to Z',
    'price_asc'  => 'Price low to high',
    'price_desc' => 'Price high to low',
    'newest'     => 'Newest',
];
$orderBy = [
    'title'      => 'title ASC',
    'price_asc'  => 'price ASC, title ASC',
    'price_desc' => 'price DESC, title ASC',
    'newest'     => 'release_year DESC, release_date DESC, id DESC',
];

$q     = trim((string) ($_GET['q'] ?? ''));
$genre = trim((string) ($_GET['genre'] ?? ''));
$sort  = (string) ($_GET['sort'] ?? 'title');
if (!isset($sorts[$sort])) {
    $sort = 'title';
}
$q = mb_substr($q, 0, 100);

$games = [];
$genres = [];
$dbError = false;
try {
    $genres = visible_genres();
    if ($genre !== '' && !in_array($genre, $genres, true)) {
        $genre = '';
    }
    $sql = "SELECT * FROM games WHERE status <> 'hidden'";
    $params = [];
    if ($q !== '') {
        $sql .= " AND (title LIKE ? ESCAPE '!' OR description LIKE ? ESCAPE '!' OR genre LIKE ? ESCAPE '!')";
        $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q) . '%';
        array_push($params, $like, $like, $like);
    }
    if ($genre !== '') {
        $sql .= ' AND genre = ?';
        $params[] = $genre;
    }
    $sql .= ' ORDER BY ' . $orderBy[$sort];
    $games = q($sql, $params)->fetchAll();
} catch (PDOException $e) {
    log_error($e);
    $dbError = true;
}

$count = count($games);
$countText = $count === 1 ? '1 game' : $count . ' games';

function results_html(array $games, string $q, bool $dbError): string
{
    if ($dbError) {
        return alert('error', MSG_SERVER_ERROR);
    }
    if (!$games) {
        $text = $q !== ''
            ? 'No games match "' . $q . '". Try another word or clear the filters.'
            : 'No games match those filters. Try another genre or clear the filters.';
        return empty_state('No games found', $text, 'Clear filters', url('games.php'), false);
    }
    return game_grid($games);
}

/* Partial response for the live filter bar. */
if (isset($_GET['partial'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['html' => results_html($games, $q, $dbError), 'count' => $dbError ? '' : $countText]);
    exit;
}

$pageTitle = 'Games';
$active = 'games';
$pageDescription = 'Every Novaspark title. Search, filter by genre and buy in a few clicks.';
require __DIR__ . '/includes/header.php';
?>
<div class="container page">
  <header class="page-head page-head--tight">
    <h1 class="h1">Games</h1>
    <p class="lead">Every Novaspark title, built in-house. Search, filter by genre, and buy in a few clicks. All prices in GBP; no payment is taken.</p>
  </header>

  <form class="filter-bar filter-bar--sticky" method="get" action="<?= e(url('games.php')) ?>" role="search" aria-label="Filter games" data-filter-form>
    <div class="filter-bar__row">
      <div class="field">
        <label class="visually-hidden" for="q">Search games</label>
        <div class="filter-bar__search">
          <?= icon('search') ?>
          <input class="control" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Search games" autocomplete="off" maxlength="100">
          <button type="button" class="filter-bar__clear" data-clear-search aria-label="Clear search"<?= $q === '' ? ' hidden' : '' ?>><?= icon('close') ?></button>
        </div>
      </div>
      <div class="filter-bar__sort">
        <?= field('sort', 'Sort by', ['type' => 'select', 'value' => $sort, 'options' => $sorts]) ?>
      </div>
    </div>
    <fieldset>
      <legend class="label">Genre</legend>
      <div class="chips" role="radiogroup" aria-label="Genre">
        <label class="chip">
          <input type="radio" name="genre" value=""<?= $genre === '' ? ' checked' : '' ?>>
          <span><?= icon('check') ?>All genres</span>
        </label>
        <?php foreach ($genres as $g): ?>
          <label class="chip">
            <input type="radio" name="genre" value="<?= e($g) ?>"<?= $genre === $g ? ' checked' : '' ?>>
            <span><?= icon('check') ?><?= e($g) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <button type="submit" class="btn btn--secondary btn--compact filter-bar__submit">Show results</button>
  </form>

  <p class="result-count mono" role="status" aria-live="polite" data-result-count><?= $dbError ? '' : e($countText) ?></p>
  <div data-results>
    <?= results_html($games, $q, $dbError) ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
