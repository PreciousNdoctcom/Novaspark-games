<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
 
session_start();
/*
 * Allows only logged-in users to view order history.
 * Unauthenticated visitors are redirected to the login page.
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Connect to database
$host   = "localhost";
$user   = "root";
$pass   = "";
$db     = "novaspark_db";
 
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database Connection Terminated: " . $conn->connect_error);
}

/*
 * Schema check queries the database metadata to check whether
 * the order_date column exists. If the column
 * is missing (e.g. the database was set up without it), we fall back to a
 * query that omits the date rather than throwing an error.
 */
$col_check  = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_date'");
$has_date   = $col_check && $col_check->num_rows > 0;
 
if ($has_date) {
     /*
     * Join query links orders to games using the foreign key game_id.
     * Only returns orders belonging to the currently logged-in user ($_SESSION['user_id']).
     * Results are sorted newest first so the most recent order appears at the top.
     */
    $sql = "
        SELECT o.id, g.title, o.quantity, o.total_price, o.order_date
        FROM   orders o
        JOIN   games  g ON g.id = o.game_id
        WHERE  o.user_id = ?
        ORDER  BY o.order_date DESC
    ";
} else {
   // Fallback query if order_date column is absent, uses NULL as a placeholder
    $sql = "
        SELECT o.id, g.title, o.quantity, o.total_price, NULL AS order_date
        FROM   orders o
        JOIN   games  g ON g.id = o.game_id
        WHERE  o.user_id = ?
        ORDER  BY o.id DESC
    ";
}
// Prepared statement binds the session user_id to the WHERE clause 
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
// Collect all rows into a PHP array so the connection can be closed before rendering
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders — Novaspark Games</title>
  <link rel="stylesheet" href="Stylesheet.css">
 
  <style>
    .orders-layout {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px 80px;
    }
 
    .orders-summary {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      margin-bottom: 28px;
    }
    .orders-count {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--muted, #888);
    }
    .orders-total-spend {
      font-family: var(--font-mono);
      font-size: 0.85rem;
      color: var(--accent2);
      font-weight: bold;
    }
 
    .order-row {
      background: var(--panel, #111);
      border: 1px solid var(--border);
      border-left: 3px solid var(--accent);
      border-radius: 4px;
      padding: 18px 22px;
      margin-bottom: 14px;
      display: grid;
      grid-template-columns: auto 1fr auto auto;
      align-items: center;
      gap: 20px;
      transition: border-color 0.2s, background 0.2s;
    }
    .order-row:hover {
      border-color: var(--accent);
      background: rgba(93,252,141,0.03);
    }
    @media (max-width: 600px) {
      .order-row { grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; }
      .order-id   { grid-column: 1; grid-row: 1; }
      .order-game { grid-column: 2; grid-row: 1; }
      .order-qty  { grid-column: 1; grid-row: 2; }
      .order-meta { grid-column: 2; grid-row: 2; text-align: right; }
    }
 
    .order-id {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      color: var(--muted, #888);
      letter-spacing: 0.05em;
      white-space: nowrap;
    }
    .order-id span { color: var(--accent); }
 
    .order-game {
      font-family: var(--font-display);
      font-size: 1.05rem;
      color: var(--text, #eee);
    }
 
    .order-qty {
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: var(--muted, #888);
      white-space: nowrap;
    }
    .order-qty strong { color: var(--text, #eee); }
 
    .order-meta { text-align: right; }
    .order-price {
      font-family: var(--font-mono);
      font-size: 1rem;
      font-weight: bold;
      color: var(--accent2);
      display: block;
    }
    .order-date {
      font-family: var(--font-mono);
      font-size: 0.68rem;
      color: var(--muted, #888);
      display: block;
      margin-top: 4px;
    }
 
    .orders-empty {
      text-align: center;
      padding: 80px 20px;
      border: 1px dashed var(--border);
      border-radius: 4px;
    }
    .orders-empty p {
      color: var(--muted, #888);
      font-family: var(--font-mono);
      font-size: 0.85rem;
      margin-bottom: 24px;
    }
  </style>
</head>
<body>
 
  <header id="site-header">
    <div class="header-inner">
      <div class="site-logo">NOVASPARK GAMES</div>
      <nav class="site-nav" aria-label="Main navigation">
        <a href="Home_Page.html" class="nav-link">Home</a>
        <a href="products.php"   class="nav-link">Products</a>
        <a href="About.html"     class="nav-link">About Us</a>
        <a href="contact.php"   class="nav-link">Contact Us</a>
        <a href="search.php"     class="nav-link">Search</a>
        <a href="order.php"      class="nav-link">Order</a>
        <a href="links.html"     class="nav-link">Useful Links</a>
 
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="my_orders.php" class="nav-link nav-link--active">My Orders</a>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="nav-link" style="color: var(--accent2);">Control Room</a>
          <?php endif; ?>
          <a href="logout.php" class="nav-link" style="color: var(--accent);">Logout</a>
        <?php else: ?>
          <a href="login.php"    class="nav-link">Login</a>
          <a href="register.php" class="nav-link">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
 
  <main id="main-content">
    <div class="orders-layout">
 
      <h1 class="hero-title" style="margin-bottom: 8px;">My Orders</h1>
      <div class="divider" style="margin-bottom: 32px;"><span>Order History</span></div>
 
      <?php if (empty($orders)): ?>

  <!-- Empty state shown when the user has placed no orders yet -->
        <div class="orders-empty">
          <p>You have not placed any orders yet.</p>
          <a href="order.php" class="btn-primary" style="text-decoration: none; display: inline-block;">
            Browse the Store
          </a>
        </div>
 
      <?php else:
      /*
         * Array_column() extracts all total_price values
         * into a flat array, then array_sum() adds them together.
         * This avoids a second database query just for the aggregate.
         */
        $grand_total = array_sum(array_column($orders, 'total_price'));
      ?>
 
        <div class="orders-summary">
          <span class="orders-count">
            <?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?> on record
          </span>
          <span class="orders-total-spend">
            Total spent: £<?= number_format($grand_total, 2) ?>
          </span>
        </div>
 
        <?php foreach ($orders as $o): ?>
          <div class="order-row">
 
            <div class="order-id">
                  <!-- str_pad() zero-pads the order ID for consistent formatting -->
              Order <span>#<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></span>
            </div>
 
            <div class="order-game">
              <?= htmlspecialchars($o['title']) ?>
            </div>
 
            <div class="order-qty">
              <strong><?= (int)$o['quantity'] ?></strong>
              licence<?= $o['quantity'] != 1 ? 's' : '' ?>
            </div>
 
            <div class="order-meta">
              <span class="order-price">£<?= number_format($o['total_price'], 2) ?></span>
              <span class="order-date">
                <!-- date() formats the MySQL Timestamp into a human-readable string -->
                <?= $o['order_date'] ? date('d M Y, H:i', strtotime($o['order_date'])) : 'Date unavailable' ?>
              </span>
            </div>
 
          </div>
        <?php endforeach; ?>
 
        <div style="margin-top: 32px; text-align: center;">
          <a href="order.php" class="btn-primary" style="text-decoration: none; display: inline-block;">
            Place Another Order
          </a>
        </div>
 
      <?php endif; ?>
 
    </div>
  </main>
 
  <footer id="site-footer">
    <div class="footer-inner">
      <span class="footer-brand">Novaspark Games</span>
      <span class="footer-sep">|</span>
      <span class="footer-note">&copy; 2026 Novaspark Games — All prices in GBP</span>
    </div>
  </footer>
 
</body>
</html>
 