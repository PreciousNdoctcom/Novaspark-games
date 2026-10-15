<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
 
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "novaspark_db";
 
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Terminated: " . $conn->connect_error);
}
 
$sql = "SELECT id, title, price FROM games ORDER BY title ASC";
$games_result = $conn->query($sql);
$games = [];
if ($games_result && $games_result->num_rows > 0) {
    while ($g = $games_result->fetch_assoc()) {
        $games[] = $g;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart — Novaspark Games</title>
  <link rel="stylesheet" href="Stylesheet.css">
 
  <style>
 
    :root {
      --c-bg:       #08080f;
      --c-surface:  #10101c;
      --c-card:     #14141f;
      --c-border:   rgba(255,255,255,0.07);
      --c-border-h: rgba(93, 252, 141, 0.3);
      --c-accent-dim: rgba(93, 252, 141, 0.09);
      --c-selected: rgba(93, 252, 141, 0.06);
      --c-txt:      #d4d4e8;
      --c-muted:    #6b6b8a;
    }
 
    body {
      background: var(--c-bg);
      color: var(--c-txt);
      font-family: var(--font-body, sans-serif);
    }
 
   
    .store-hero {
      padding: 72px 5% 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .store-hero::before {
      content: '';
      position: absolute;
      top: -80px; left: 50%;
      transform: translateX(-50%);
      width: 600px; height: 300px;
      background: radial-gradient(ellipse at center, rgba(93,252,141,0.07) 0%, transparent 70%);
      pointer-events: none;
    }
    .store-hero h1 {
      font-family: var(--font-display, sans-serif);
      font-size: clamp(2.4rem, 5vw, 4rem);
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #fff;
      margin: 0 0 12px;
    }
    .store-hero h1 span { color: var(--accent); }
    .store-hero p {
      color: var(--c-muted);
      font-size: 0.95rem;
      max-width: 480px;
      margin: 0 auto;
      line-height: 1.6;
    }
 
    .store-layout {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 32px;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 5% 80px;
      align-items: start;
    }
    @media (max-width: 900px) {
      .store-layout { grid-template-columns: 1fr; }
    }
 
    .game-grid-label {
      font-family: var(--font-mono, monospace);
      font-size: 0.72rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--c-muted);
      margin: 0 0 16px;
    }
    .game-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
      gap: 14px;
    }
    .game-card {
      background: var(--c-card);
      border: 1px solid var(--c-border);
      border-radius: 4px;
      padding: 22px 20px 18px;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s, transform 0.18s;
      position: relative;
      overflow: hidden;
      user-select: none;
    }
    .game-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: var(--accent);
      opacity: 0;
      transition: opacity 0.2s;
    }
    .game-card:hover {
      border-color: var(--c-border-h);
      transform: translateY(-3px);
    }
    .game-card:hover::before { opacity: 1; }
 
    .game-card.selected {
      background: var(--c-selected);
      border-color: var(--accent);
    }
    .game-card.selected::before { opacity: 1; }
 
    .game-card-icon {
      width: 42px; height: 42px;
      border-radius: 4px;
      background: var(--c-accent-dim);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px;
    }
    .game-card-icon svg {
      width: 22px; height: 22px;
      stroke: var(--accent);
      fill: none; stroke-width: 1.6;
      stroke-linecap: round; stroke-linejoin: round;
    }
 
    .game-card-title {
      font-family: var(--font-display, sans-serif);
      font-size: 1.05rem;
      font-weight: 600;
      color: var(--text, #fff);
      margin: 0 0 6px;
      letter-spacing: 0.02em;
    }
    .game-card-price {
      font-family: var(--font-mono, monospace);
      font-size: 0.82rem;
      color: var(--accent2);
      font-weight: bold;
    }
    .game-card-check {
      position: absolute;
      top: 12px; right: 12px;
      width: 20px; height: 20px;
      border-radius: 50%;
      background: var(--accent);
      display: flex; align-items: center; justify-content: center;
      opacity: 0;
      transform: scale(0.6);
      transition: opacity 0.18s, transform 0.18s;
    }
    .game-card.selected .game-card-check {
      opacity: 1;
      transform: scale(1);
    }
    .game-card-check svg {
      width: 11px; height: 11px;
      stroke: #000; fill: none;
      stroke-width: 2.5;
      stroke-linecap: round; stroke-linejoin: round;
    }
 
    .order-panel {
      background: var(--c-surface);
      border: 1px solid var(--c-border);
      border-radius: 4px;
      padding: 28px 24px;
      position: sticky;
      top: 24px;
    }
    .panel-title {
      font-family: var(--font-display, sans-serif);
      font-size: 1rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--text, #fff);
      margin: 0 0 24px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .panel-title-badge {
      background: var(--accent);
      color: var(--header-bg, #0d0d0d);
      font-family: var(--font-mono, monospace);
      font-size: 0.65rem;
      font-weight: bold;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 2px;
    }
 
    .selection-display {
      background: var(--c-card);
      border: 1px solid var(--c-border);
      border-radius: 4px;
      padding: 14px 16px;
      margin-bottom: 20px;
      min-height: 64px;
      display: flex;
      align-items: center;
    }
    .selection-placeholder {
      color: var(--c-muted);
      font-family: var(--font-mono, monospace);
      font-size: 0.82rem;
      font-style: italic;
    }
    .selection-info { display: none; width: 100%; }
    .selection-info.visible { display: flex; align-items: center; justify-content: space-between; }
    .sel-title {
      font-family: var(--font-display, sans-serif);
      font-size: 1rem;
      font-weight: 600;
      color: var(--text, #fff);
    }
    .sel-price {
      font-family: var(--font-mono, monospace);
      color: var(--accent2);
      font-weight: bold;
      font-size: 0.9rem;
    }
 
    .qty-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .qty-label {
      font-family: var(--font-mono, monospace);
      font-size: 0.72rem;
      color: var(--c-muted);
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }
    .qty-stepper {
      display: flex;
      align-items: center;
      background: var(--c-card);
      border: 1px solid var(--c-border);
      border-radius: 2px;
      overflow: hidden;
    }
    .qty-btn {
      background: none;
      border: none;
      color: var(--accent);
      font-size: 1.2rem;
      width: 36px; height: 36px;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.15s;
    }
    .qty-btn:hover { background: var(--c-accent-dim); }
    .qty-val {
      font-family: var(--font-mono, monospace);
      font-size: 1rem;
      font-weight: bold;
      color: var(--text, #fff);
      min-width: 32px;
      text-align: center;
    }
 
    .total-row {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      border-top: 1px solid rgba(93,252,141,0.08);
      padding-top: 16px;
      margin-bottom: 22px;
    }
    .total-label {
      font-family: var(--font-mono, monospace);
      font-size: 0.72rem;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--c-muted);
    }
    .total-val {
      font-family: var(--font-mono, monospace);
      font-size: 1.6rem;
      font-weight: bold;
      color: var(--accent2);
    }
 
    .field-block { margin-bottom: 14px; }
    .field-block label {
      display: block;
      font-family: var(--font-mono, monospace);
      font-size: 0.7rem;
      color: var(--c-muted);
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 6px;
    }
    .field-block input {
      width: 100%;
      box-sizing: border-box;
      background: var(--c-card);
      border: 1px solid var(--border, rgba(255,255,255,0.07));
      border-radius: 2px;
      padding: 10px 14px;
      color: var(--text, #d4d4e8);
      font-family: var(--font-mono, monospace);
      font-size: 0.85rem;
      outline: none;
      transition: border-color 0.18s;
    }
    .field-block input::placeholder { color: var(--c-muted); }
    .field-block input:focus { border-color: var(--accent); }
 
    .btn-order {
      width: 100%;
      padding: 12px 14px;
      background-color: transparent;
      border: 1px solid var(--accent);
      border-radius: 2px;
      font-family: var(--font-mono, monospace);
      font-size: 0.82rem;
      font-weight: bold;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--accent);
      cursor: pointer;
      transition: background-color 0.2s, color 0.2s, opacity 0.2s;
    }
    .btn-order:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }
    .btn-order:not(:disabled):hover {
      background-color: var(--accent);
      color: var(--header-bg, #0d0d0d);
    }
 
    .form-msg {
      font-family: var(--font-mono, monospace);
      font-size: 0.75rem;
      margin-top: 12px;
      min-height: 20px;
      text-align: center;
      color: #ff6b6b;
    }
    .form-msg.ok { color: var(--accent); }
 
    .games-empty {
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 0;
      color: var(--c-muted);
    }
  </style>
</head>
<body>
 
  <header id="site-header">
    <div class="header-inner">
      <div class="site-logo">NOVASPARK GAMES</div>
      <nav class="site-nav" aria-label="Main navigation">
        <a href="home_page.html" class="nav-link">Home</a>
        <a href="products.php"   class="nav-link">Products</a>
        <a href="About.html"     class="nav-link">About Us</a>
        <a href="contact.php"   class="nav-link">Contact Us</a>
        <a href="search.php"     class="nav-link">Search</a>
        <a href="order.php"      class="nav-link nav-link--active">Order</a>
        <a href="links.html"     class="nav-link">Useful Links</a>
 
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="my_orders.php" class="nav-link">My Orders</a>
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
 
    <div class="store-hero">
      <h1>Game <span>Store</span></h1>
      <p>Select a title, choose your licence quantity, and submit your order.
         No payment is taken — this is a simulated ordering system.
         All prices are in GBP (£).</p>
    </div>
 
    <div class="store-layout">
 
      <div>
        <p class="game-grid-label">Available Titles — <?= count($games) ?> products</p>
        <div class="game-grid" id="game-grid">
 
          <?php if (empty($games)): ?>
            <div class="games-empty">No games found in the database.</div>
          <?php else: ?>
            <?php foreach ($games as $g): ?>
              <div class="game-card"
                   data-id="<?= $g['id'] ?>"
                   data-title="<?= htmlspecialchars($g['title']) ?>"
                   data-price="<?= number_format($g['price'], 2) ?>"
                   onclick="selectGame(this)">
 
                <div class="game-card-check">
                  <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                </div>
 
                <div class="game-card-icon">
                  <svg viewBox="0 0 24 24">
                    <rect x="2" y="6" width="20" height="14" rx="2"/>
                    <path d="M8 2h8M12 6v4M9 13h6M12 11v4"/>
                  </svg>
                </div>
 
                <p class="game-card-title"><?= htmlspecialchars($g['title']) ?></p>
                <p class="game-card-price">£<?= number_format($g['price'], 2) ?> / licence</p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
 
        </div>
      </div>
 
      <aside class="order-panel">
        <p class="panel-title">
          Your Order
          <span class="panel-title-badge">SECURE</span>
        </p>
 
        <div class="selection-display">
          <p class="selection-placeholder" id="sel-placeholder">← Select a game from the grid</p>
          <div class="selection-info" id="sel-info">
            <span class="sel-title" id="sel-title-txt"></span>
            <span class="sel-price" id="sel-price-txt"></span>
          </div>
        </div>

        <div class="qty-row">
          <span class="qty-label">Licences</span>
          <div class="qty-stepper">
            <button class="qty-btn" onclick="adjustQty(-1)" aria-label="Decrease quantity">−</button>
            <span class="qty-val" id="qty-display">1</span>
            <button class="qty-btn" onclick="adjustQty(1)" aria-label="Increase quantity">+</button>
          </div>
        </div>
 
        <div class="total-row">
          <span class="total-label">Total</span>
          <span class="total-val" id="total-display">£0.00</span>
        </div>
 
        <form action="process_order.php" method="post" id="order-form" onsubmit="return validateForm(event)">
          <input type="hidden" name="game_id"  id="hidden-game-id" value="">
          <input type="hidden" name="quantity"  id="hidden-qty"     value="1">
 
          <div class="field-block">
            <label for="order-name">Your Name</label>
            <input type="text"  id="order-name"  name="customer_name"
                   placeholder="Full name or designation" autocomplete="name">
          </div>
          <div class="field-block">
            <label for="order-email">Email Address</label>
            <input type="email" id="order-email" name="customer_email"
                   placeholder="you@organisation.com" autocomplete="email">
          </div>
 
          <button type="submit" class="btn-order" id="submit-btn" disabled>
            Select a Game to Continue
          </button>
          <p class="form-msg" id="form-msg"></p>
        </form>
      </aside>
 
    </div>
  </main>
 
  <footer id="site-footer">
    <div class="footer-inner">
      <span class="footer-brand">Novaspark Games</span>
      <span class="footer-sep">|</span>
      <a href="mailto:info@novasparkgames.example.com" class="footer-link">info@novasparkgames.example.com</a>
      <span class="footer-sep">|</span>
      <span class="footer-note">&copy; 2026 Novaspark Games — All prices in GBP</span>
    </div>
  </footer>
 
  <script src="script.js"></script>
  <script>
    let selectedPrice = 0;
    let qty = 1;
 
    function selectGame(card) {
      document.querySelectorAll('.game-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
 
      const title = card.dataset.title;
      const price = parseFloat(card.dataset.price);
      const id    = card.dataset.id;
 
      selectedPrice = price;
      document.getElementById('hidden-game-id').value = id;
      document.getElementById('sel-placeholder').style.display = 'none';
      document.getElementById('sel-info').classList.add('visible');
      document.getElementById('sel-title-txt').textContent = title;
      document.getElementById('sel-price-txt').textContent = '£' + price.toFixed(2);
 
      document.getElementById('submit-btn').disabled = false;
      document.getElementById('submit-btn').textContent = 'Place Order';
      updateTotal();
    }
 
    function adjustQty(delta) {
      qty = Math.max(1, Math.min(10, qty + delta));
      document.getElementById('qty-display').textContent = qty;
      document.getElementById('hidden-qty').value = qty;
      updateTotal();
    }
 
    function updateTotal() {
      document.getElementById('total-display').textContent = '£' + (selectedPrice * qty).toFixed(2);
    }
 
    function validateForm(e) {
      const msg    = document.getElementById('form-msg');
      const name   = document.getElementById('order-name').value.trim();
      const email  = document.getElementById('order-email').value.trim();
      const gameId = document.getElementById('hidden-game-id').value;
 
      if (!gameId)  { msg.textContent = 'Please select a game.';               e.preventDefault(); return false; }
      if (!name)    { msg.textContent = 'Please enter your name.';             e.preventDefault(); return false; }
      if (!email)   { msg.textContent = 'Please enter your email address.';    e.preventDefault(); return false; }
 
      const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      if (!emailOk) { msg.textContent = 'Please enter a valid email address.'; e.preventDefault(); return false; }
 
      msg.className = 'form-msg ok';
      msg.textContent = 'Submitting…';
      return true;
    }
  </script>
</body>
</html>