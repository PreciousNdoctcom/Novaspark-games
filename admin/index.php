<?php
/* Admin: manage users and games, view every order. */
require dirname(__DIR__) . '/includes/helpers.php';

$admin = require_admin();
$tabs = ['users' => 'Users', 'games' => 'Games', 'orders' => 'Orders'];
$tab = (string) ($_GET['tab'] ?? $_POST['tab'] ?? 'users');
if (!isset($tabs[$tab])) {
    $tab = 'users';
}

$blankGame = ['id' => 0, 'title' => '', 'genre' => '', 'price' => '', 'release_year' => date('Y'), 'status' => 'available',
              'release_date' => '', 'platforms' => '', 'description' => '', 'image_url' => ''];
$formGame = null;
$formErrors = [];
$panelError = '';

function back_to(string $tab, array $result, array $extra = []): void
{
    flash('admin_result', $result);
    redirect('admin/index.php?' . http_build_query(['tab' => $tab] + $extra));
}

/* ---------- Actions ---------- */
if (is_post()) {
    $action = post('action');
    if (!csrf_check()) {
        $panelError = 'This form expired. Try that again.';
    } else {
        try {
            if ($action === 'delete_user') {
                $id = (int) post('user_id');
                $target = q('SELECT id, username FROM users WHERE id = ?', [$id])->fetch();
                if (!$target) {
                    back_to('users', ['error', 'That user no longer exists.']);
                }
                if ($id === $admin['id']) {
                    back_to('users', ['error', "You can't delete the account you're logged in with."]);
                }
                $n = (int) q('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$id])->fetchColumn();
                if ($n > 0) {
                    back_to('users', ['error', $target['username'] . ' has ' . $n . ($n === 1 ? ' order' : ' orders') . ", so the account can't be deleted. Order history must stay complete."]);
                }
                q('DELETE FROM users WHERE id = ?', [$id]);
                back_to('users', ['success', 'Deleted ' . $target['username'] . '.']);
            }

            if ($action === 'save_game') {
                $tab = 'games';
                $formGame = [
                    'id'           => (int) post('game_id'),
                    'title'        => post('title'),
                    'genre'        => post('genre'),
                    'price'        => post('price'),
                    'release_year' => post('release_year'),
                    'status'       => post('status'),
                    'release_date' => post('release_date'),
                    'platforms'    => post('platforms'),
                    'description'  => post('description'),
                    'image_url'    => post('image_url'),
                ];
                $g = $formGame;
                if ($g['title'] === '')            $formErrors['title'] = 'Enter the title.';
                elseif (mb_strlen($g['title']) > 100) $formErrors['title'] = 'Use 100 characters or fewer.';
                if ($g['genre'] === '')            $formErrors['genre'] = 'Enter the genre.';
                elseif (mb_strlen($g['genre']) > 50) $formErrors['genre'] = 'Use 50 characters or fewer.';
                if ($g['price'] === '' || !is_numeric($g['price']) || (float) $g['price'] < 0 || (float) $g['price'] > 999.99) {
                    $formErrors['price'] = 'Enter a price in pounds, like 49.99.';
                }
                if (!preg_match('/^\d{4}$/', $g['release_year']) || (int) $g['release_year'] < 1980 || (int) $g['release_year'] > 2100) {
                    $formErrors['release_year'] = 'Enter a four-digit year, like 2026.';
                }
                if (!isset(GAME_STATUSES[$g['status']])) $formErrors['status'] = 'Choose a status.';
                if ($g['release_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $g['release_date'])) {
                    $formErrors['release_date'] = 'Enter a date like 2026-10-19.';
                }
                if ($g['status'] === 'coming_soon' && $g['release_date'] === '') {
                    $formErrors['release_date'] = 'Enter the release date for a game that is coming soon.';
                }
                if ($g['description'] === '')      $formErrors['description'] = 'Enter the description.';
                if ($g['image_url'] !== '' && !preg_match('#^(https?://|assets/)[^\s"\'<>]+$#i', $g['image_url'])) {
                    $formErrors['image_url'] = 'Use a full https:// address or a path starting assets/.';
                }

                if (!$formErrors) {
                    $params = [$g['title'], slugify($g['title']), $g['genre'], round((float) $g['price'], 2), $g['description'], (int) $g['release_year'],
                               $g['image_url'] !== '' ? $g['image_url'] : 'assets/img/games/fallback.svg', $g['status'],
                               $g['release_date'] !== '' ? $g['release_date'] : null, $g['platforms'] !== '' ? $g['platforms'] : null];
                    if ($g['id'] > 0) {
                        $params[] = $g['id'];
                        q('UPDATE games SET title = ?, slug = ?, genre = ?, price = ?, description = ?, release_year = ?, image_url = ?, status = ?, release_date = ?, platforms = ? WHERE id = ?', $params);
                        $savedId = $g['id'];
                    } else {
                        q('INSERT INTO games (title, slug, genre, price, description, release_year, image_url, status, release_date, platforms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $params);
                        $savedId = (int) db()->lastInsertId();
                    }
                    flash('admin_highlight', $savedId);
                    back_to('games', ['success', 'Saved ' . $g['title'] . '.']);
                }
            }

            if ($action === 'delete_game' || $action === 'hide_game') {
                $id = (int) post('game_id');
                $target = q('SELECT id, title FROM games WHERE id = ?', [$id])->fetch();
                if (!$target) {
                    back_to('games', ['error', 'That game no longer exists.']);
                }
                if ($action === 'hide_game') {
                    q("UPDATE games SET status = 'hidden' WHERE id = ?", [$id]);
                    back_to('games', ['success', $target['title'] . ' is hidden from the store. Its orders keep their record.']);
                }
                $n = (int) q('SELECT COUNT(*) FROM orders WHERE game_id = ?', [$id])->fetchColumn();
                if ($n > 0) {
                    back_to('games', ['error', $target['title'] . ' has ' . $n . ($n === 1 ? ' order' : ' orders') . ", so it can't be deleted. Hide it from the store instead?"], ['hide' => $id]);
                }
                q('DELETE FROM games WHERE id = ?', [$id]);
                back_to('games', ['success', 'Deleted ' . $target['title'] . '.']);
            }
        } catch (PDOException $e) {
            log_error($e);
            $panelError = MSG_SERVER_ERROR;
        }
    }
}

$result = flash('admin_result');
$highlight = (int) flash('admin_highlight');

/* ---------- Data for the current tab ---------- */
$users = $games = $orders = $allGames = [];
$genres = [];
$counts = ['users' => 0, 'games' => 0, 'orders' => 0];
$orderFilter = (int) ($_GET['game'] ?? 0);
try {
    $counts['users'] = (int) q('SELECT COUNT(*) FROM users')->fetchColumn();
    $counts['games'] = (int) q('SELECT COUNT(*) FROM games')->fetchColumn();
    $counts['orders'] = (int) q('SELECT COUNT(*) FROM orders')->fetchColumn();

    if ($tab === 'users') {
        $users = q('SELECT u.id, u.username, u.email, u.role, u.created_at, COUNT(o.id) AS orders FROM users u LEFT JOIN orders o ON o.user_id = u.id GROUP BY u.id, u.username, u.email, u.role, u.created_at ORDER BY u.id ASC')->fetchAll();
    }
    if ($tab === 'games') {
        $games = q('SELECT g.*, COUNT(o.id) AS orders FROM games g LEFT JOIN orders o ON o.game_id = g.id GROUP BY g.id, g.title, g.slug, g.genre, g.price, g.description, g.release_year, g.image_url, g.status, g.release_date, g.platforms ORDER BY g.id DESC')->fetchAll();
        $genres = q('SELECT DISTINCT genre FROM games ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);
        if ($formGame === null) {
            if (isset($_GET['new'])) {
                $formGame = $blankGame;
            } elseif (isset($_GET['edit'])) {
                $row = q('SELECT * FROM games WHERE id = ?', [(int) $_GET['edit']])->fetch();
                $formGame = $row ?: null;
                if (!$row) $panelError = 'That game no longer exists.';
            }
        }
    }
    if ($tab === 'orders') {
        $allGames = q('SELECT id, title FROM games ORDER BY title')->fetchAll();
        $sql = 'SELECT o.*, u.username, g.title FROM orders o JOIN users u ON u.id = o.user_id JOIN games g ON g.id = o.game_id';
        $params = [];
        if ($orderFilter > 0) {
            $sql .= ' WHERE o.game_id = ?';
            $params[] = $orderFilter;
        }
        $orders = q($sql . ' ORDER BY o.order_date DESC, o.id DESC', $params)->fetchAll();
    }
} catch (PDOException $e) {
    log_error($e);
    $panelError = MSG_SERVER_ERROR;
}

$hideOffer = (int) ($_GET['hide'] ?? 0);

$pageTitle = 'Admin';
$active = 'admin';
require APP_ROOT . '/includes/header.php';
?>
<div class="container page">
  <header class="page-head page-head--tight">
    <p class="label">Signed in as <?= e($admin['username']) ?></p>
    <h1 class="h1">Admin</h1>
  </header>

  <nav class="tabs" aria-label="Admin sections">
    <?php foreach ($tabs as $key => $label): ?>
      <a href="<?= e(url('admin/index.php?tab=' . $key)) ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="count"><?= $counts[$key] ?></span></a>
    <?php endforeach; ?>
  </nav>

  <div class="alerts" aria-live="polite">
    <?php if ($panelError): ?><?= alert('error', $panelError) ?><?php endif; ?>
    <?php if ($result): ?>
      <?php if ($hideOffer > 0 && $result[0] === 'error'): ?>
        <?= alert('error', '<p>' . e($result[1]) . '</p><form class="alert__actions" method="post" action="' . e(url('admin/index.php')) . '">' . csrf_field()
            . '<input type="hidden" name="action" value="hide_game"><input type="hidden" name="tab" value="games"><input type="hidden" name="game_id" value="' . $hideOffer . '">'
            . '<button type="submit" class="btn btn--secondary btn--compact">Hide from store</button>'
            . '<a class="btn btn--ghost btn--compact" href="' . e(url('admin/index.php?tab=games')) . '">Keep it</a></form>', ['html' => true]) ?>
      <?php else: ?>
        <?= alert($result[0], $result[1]) ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <?php if ($tab === 'users'): ?>
    <section aria-labelledby="users-title">
      <h2 class="visually-hidden" id="users-title">Users</h2>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th scope="col" class="num">ID</th><th scope="col">Username</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Joined</th><th scope="col" class="num">Orders</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): $isSelf = (int) $u['id'] === $admin['id']; $n = (int) $u['orders']; ?>
            <tr>
              <td class="num" data-label="ID"><?= (int) $u['id'] ?></td>
              <td data-label="Username"><strong><?= e($u['username']) ?></strong><?= $isSelf ? ' <span class="small muted">(you)</span>' : '' ?></td>
              <td data-label="Email"><?= e($u['email']) ?></td>
              <td data-label="Role"><?= role_badge($u['role']) ?></td>
              <td class="mono" data-label="Joined"><?= e(fmt_date($u['created_at'])) ?></td>
              <td class="num" data-label="Orders"><?= $n ?></td>
              <td class="row-actions-cell" data-label="Actions">
                <div class="row-actions">
                  <?php if ($isSelf): ?>
                    <span class="small muted">Can't delete yourself</span>
                  <?php elseif ($n > 0): ?>
                    <span class="small muted">Has orders, so can't be deleted</span>
                  <?php else: ?>
                    <form method="post" action="<?= e(url('admin/index.php')) ?>"
                          data-confirm="Delete <?= e($u['username']) ?>?" data-confirm-body="This can't be undone." data-confirm-action="Delete user">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="tab" value="users">
                      <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                      <button type="submit" class="btn btn--danger btn--compact">Delete<span class="visually-hidden"> <?= e($u['username']) ?></span></button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($tab === 'games'): ?>
    <section aria-labelledby="games-title">
      <div class="admin-toolbar">
        <h2 class="h3" id="games-title">Games</h2>
        <?php if ($formGame === null): ?>
          <a class="btn btn--primary btn--compact" href="<?= e(url('admin/index.php?tab=games&new=1')) ?>">Add game</a>
        <?php endif; ?>
      </div>

      <?php if ($formGame !== null): $g = $formGame; $isEdit = (int) $g['id'] > 0; ?>
        <form class="info-card admin-form form" method="post" action="<?= e(url('admin/index.php?tab=games')) ?>" data-validate aria-labelledby="game-form-title">
          <h3 class="info-card__title" id="game-form-title"><?= $isEdit ? 'Edit ' . e($g['title']) : 'Add game' ?></h3>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save_game">
          <input type="hidden" name="tab" value="games">
          <input type="hidden" name="game_id" value="<?= (int) $g['id'] ?>">
          <div data-error-summary><?= error_summary($formErrors) ?></div>
          <div class="admin-form__grid">
            <?= field('title', 'Title', ['value' => $g['title'], 'error' => $formErrors['title'] ?? '', 'required' => true, 'attrs' => 'maxlength="100"']) ?>
            <?= field('genre', 'Genre', ['value' => $g['genre'], 'error' => $formErrors['genre'] ?? '', 'required' => true, 'helper' => 'Pick an existing genre or type a new one.', 'attrs' => 'list="genre-list" maxlength="50"']) ?>
            <?= field('price', 'Price in £', ['type' => 'number', 'value' => $g['price'] !== '' ? number_format((float) $g['price'], 2, '.', '') : '', 'error' => $formErrors['price'] ?? '', 'required' => true, 'attrs' => 'min="0" max="999.99" step="0.01" inputmode="decimal" data-msg-range="Enter a price in pounds, like 49.99."']) ?>
            <?= field('release_year', 'Release year', ['type' => 'number', 'value' => (string) $g['release_year'], 'error' => $formErrors['release_year'] ?? '', 'required' => true, 'attrs' => 'min="1980" max="2100" step="1" inputmode="numeric" data-msg-range="Enter a four-digit year, like 2026."']) ?>
            <?= field('status', 'Status', ['type' => 'select', 'value' => $g['status'], 'options' => GAME_STATUSES, 'error' => $formErrors['status'] ?? '', 'helper' => 'Hidden games stay in order history but leave the store.']) ?>
            <?= field('release_date', 'Release date', ['type' => 'date', 'value' => (string) $g['release_date'], 'error' => $formErrors['release_date'] ?? '', 'optional' => true, 'helper' => 'Required for Coming soon.']) ?>
            <?= field('platforms', 'Platforms', ['value' => (string) $g['platforms'], 'optional' => true, 'helper' => 'For example PC / PS5 / Xbox.', 'attrs' => 'maxlength="100"']) ?>
            <div class="span-2"><?= field('description', 'Description', ['type' => 'textarea', 'rows' => 4, 'value' => $g['description'], 'error' => $formErrors['description'] ?? '', 'required' => true]) ?></div>
            <div class="span-2"><?= field('image_url', 'Image URL', ['type' => 'text', 'value' => (string) $g['image_url'], 'error' => $formErrors['image_url'] ?? '', 'optional' => true, 'helper' => 'A local path such as assets/img/games/void-protocol.svg, or a full https:// address.', 'attrs' => 'data-image-preview="image-preview" spellcheck="false"']) ?></div>
            <div class="span-2 stack">
              <p class="field__label">Preview</p>
              <img class="image-preview" id="image-preview" src="<?= e(img_src($g['image_url'])) ?>" data-base="<?= e(base_url()) ?>" alt="Cover preview" width="320" height="180">
            </div>
          </div>
          <datalist id="genre-list">
            <?php foreach ($genres as $genre): ?><option value="<?= e($genre) ?>"></option><?php endforeach; ?>
          </datalist>
          <div class="actions">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Save changes' : 'Add game' ?></button>
            <a class="btn btn--ghost" href="<?= e(url('admin/index.php?tab=games')) ?>">Cancel</a>
          </div>
        </form>
      <?php endif; ?>

      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th scope="col">Game</th><th scope="col">Genre</th><th scope="col" class="num">Price</th><th scope="col">Status</th><th scope="col" class="num">Orders</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
          <tbody>
          <?php foreach ($games as $g): $n = (int) $g['orders']; ?>
            <tr<?= $highlight === (int) $g['id'] ? ' class="is-new"' : '' ?>>
              <td data-label="Game"><span class="row-title"><?= game_cover($g, 'row-thumb', 128, 72) ?><?= e($g['title']) ?></span></td>
              <td data-label="Genre"><?= e($g['genre']) ?></td>
              <td class="num" data-label="Price"><?= e(money($g['price'])) ?></td>
              <td data-label="Status"><?= status_badge($g['status']) ?></td>
              <td class="num" data-label="Orders"><?= $n ?></td>
              <td class="row-actions-cell" data-label="Actions">
                <div class="row-actions">
                  <a class="btn btn--secondary btn--compact" href="<?= e(url('admin/index.php?tab=games&edit=' . (int) $g['id'])) ?>">Edit<span class="visually-hidden"> <?= e($g['title']) ?></span></a>
                  <?php if ($n > 0 && $g['status'] !== 'hidden'): ?>
                    <form method="post" action="<?= e(url('admin/index.php')) ?>"
                          data-confirm="Hide <?= e($g['title']) ?> from the store?"
                          data-confirm-body="<?= e($g['title'] . ' has ' . $n . ($n === 1 ? ' order' : ' orders') . ", so it can't be deleted. Hiding removes it from Games; its orders keep their record.") ?>"
                          data-confirm-action="Hide from store" data-confirm-style="secondary">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="hide_game">
                      <input type="hidden" name="tab" value="games">
                      <input type="hidden" name="game_id" value="<?= (int) $g['id'] ?>">
                      <button type="submit" class="btn btn--danger btn--compact">Delete<span class="visually-hidden"> <?= e($g['title']) ?></span></button>
                    </form>
                  <?php elseif ($n === 0): ?>
                    <form method="post" action="<?= e(url('admin/index.php')) ?>"
                          data-confirm="Delete <?= e($g['title']) ?>?" data-confirm-body="This can't be undone." data-confirm-action="Delete game">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_game">
                      <input type="hidden" name="tab" value="games">
                      <input type="hidden" name="game_id" value="<?= (int) $g['id'] ?>">
                      <button type="submit" class="btn btn--danger btn--compact">Delete<span class="visually-hidden"> <?= e($g['title']) ?></span></button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($tab === 'orders'): ?>
    <section aria-labelledby="orders-title">
      <div class="admin-toolbar">
        <h2 class="h3" id="orders-title">Orders</h2>
        <form method="get" action="<?= e(url('admin/index.php')) ?>" class="filter-inline">
          <input type="hidden" name="tab" value="orders">
          <?php $opts = ['0' => 'All games']; foreach ($allGames as $ag) { $opts[(string) $ag['id']] = $ag['title']; } ?>
          <?= field('game', 'Filter by game', ['type' => 'select', 'value' => (string) $orderFilter, 'options' => $opts, 'attrs' => 'data-autosubmit']) ?>
          <button type="submit" class="btn btn--secondary btn--compact filter-inline__submit">Filter</button>
        </form>
      </div>
      <?php if (!$orders): ?>
        <?= empty_state('No orders yet', $orderFilter ? 'No one has ordered this game yet.' : 'Orders appear here as soon as a customer places one.') ?>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Game</th><th scope="col" class="num">Licences</th><th scope="col" class="num">Total</th><th scope="col">Date</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td class="mono" data-label="Order"><?= e(order_no($o['id'])) ?></td>
                <td data-label="Customer"><?= e($o['username']) ?></td>
                <td data-label="Game"><?= e($o['title']) ?></td>
                <td class="num" data-label="Licences"><?= (int) $o['quantity'] ?></td>
                <td class="num amber" data-label="Total"><?= e(money($o['total_price'])) ?></td>
                <td class="mono" data-label="Date"><?= e(fmt_date($o['order_date'])) ?> <?= e(fmt_time($o['order_date'])) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/footer.php'; ?>
