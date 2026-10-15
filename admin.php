<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "novaspark_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Master Database Telemetry Exception: " . $conn->connect_error);
}

$status_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'delete_user') {
        $target_user_id = intval($_POST['user_id']);
        
        if ($target_user_id === intval($_SESSION['user_id'])) {
            $error_msg = "Operation blocked: You cannot delete your own admin account while active.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            if ($stmt->execute()) {
                $status_msg = "User record purged successfully from terminal logs.";
            }
            $stmt->close();
        }
    }
    
    if ($_POST['action'] === 'save_game') {
        $game_id           = intval($_POST['game_id']);
        $game_title        = trim($_POST['title']);
        $game_genre        = trim($_POST['genre']);
        $game_price        = floatval($_POST['price']);
        $image_placeholder = trim($_POST['image_placeholder']);
        
        if (empty($game_title) || empty($game_genre)) {
            $error_msg = "Operation blocked: Game Title and Classification variables are required.";
        } else {
            if ($game_id > 0) {

                $stmt = $conn->prepare("UPDATE games SET title = ?, genre = ?, price = ?, image_placeholder = ? WHERE id = ?");
                $stmt->bind_param("ssdsi", $game_title, $game_genre, $game_price, $image_placeholder, $game_id);
                if ($stmt->execute()) { $status_msg = "Game module configuration updated successfully."; }
            } else {
                
                $stmt = $conn->prepare("INSERT INTO games (title, genre, price, image_placeholder) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssds", $game_title, $game_genre, $game_price, $image_placeholder);
                if ($stmt->execute()) { $status_msg = "New product module compiled into the catalog."; }
            }
            $stmt->close();
        }
    }
    
    if ($_POST['action'] === 'delete_game') {
        $target_game_id = intval($_POST['game_id']);
        $stmt = $conn->prepare("DELETE FROM games WHERE id = ?");
        $stmt->bind_param("i", $target_game_id);
        if ($stmt->execute()) {
            $status_msg = "Product entry detached and removed from catalog database.";
        }
        $stmt->close();
    }
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';

$edit_game = ['id' => 0, 'title' => '', 'genre' => '', 'price' => 0.00, 'image_placeholder' => '🎮'];
if ($active_tab === 'games' && isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = $conn->query("SELECT * FROM games WHERE id = $edit_id");
    if ($edit_res && $edit_res->num_rows === 1) {
        $edit_game = $edit_res->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Terminal — Novaspark Games</title>
    
    <link rel="stylesheet" href="Stylesheet.css">
    
    <style>
        .admin-dashboard {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .tab-navigation {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 30px;
            gap: 10px;
        }
        .tab-btn {
            background: none;
            border: 1px solid transparent;
            color: var(--muted);
            font-family: var(--font-mono);
            font-size: 0.85rem;
            text-transform: uppercase;
            padding: 12px 24px;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 4px 4px 0 0;
            margin-bottom: -1px;
            text-decoration: none;
        }
        .tab-btn:hover {
            color: var(--text);
            background-color: rgba(93, 252, 141, 0.05);
        }
        .tab-btn--active {
            color: var(--accent);
            border: 1px solid var(--border);
            border-bottom: 1px solid var(--panel);
            background-color: var(--panel);
        }

        .admin-panel {
            background-color: var(--panel);
            border: 1px solid var(--border);
            padding: 30px;
            border-radius: 4px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            margin-top: 15px;
        }
        .admin-table th {
            background-color: var(--header-bg);
            color: #5c9758; 
            text-transform: uppercase;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--border);
        }
        .admin-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(93, 252, 141, 0.08);
            color: var(--text);
        }
        .admin-table tr:hover {
            background-color: rgba(93, 252, 141, 0.02);
        }
        
        .inline-action-form {
            display: inline;
        }
        .btn-action-delete {
            background: none;
            border: 1px solid #5c9758; 
            color: #5c9758;
            padding: 4px 10px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            cursor: pointer;
            border-radius: 2px;
        }
        .btn-action-delete:hover {
            background-color: rgba(92, 151, 88, 0.15);
        }
        .btn-action-edit {
            background: none;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 4px 10px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            cursor: pointer;
            border-radius: 2px;
            text-decoration: none;
        }
        .btn-action-edit:hover {
            background-color: rgba(93, 252, 141, 0.15);
        }
       
        .game-manager-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        @media(max-width: 768px) {
            .game-manager-grid { grid-template-columns: 1fr; }
        }
        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: #5c9758;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .form-group input {
            background-color: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            font-family: var(--font-mono);
            font-size: 0.85rem;
            padding: 10px;
            outline: none;
        }
        .form-group input:focus { border-color: var(--accent); }
        .system-feedback {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 2px;
            border-left: 4px solid #5c9758;
            background-color: var(--header-bg);
        }
    </style>
</head>
<body>

  <header id="site-header">
    <div class="header-inner">
      <div class="site-logo">NOVASPARK GAMES</div>
      <nav class="site-nav" aria-label="System navigation">
        <a href="Home_Page.html"    class="nav-link">Home View</a>
        <a href="products.php" class="nav-link">Catalogue Dashboard</a>
        <a href="admin.php"    class="nav-link nav-link--active">Admin</a>
        <a href="logout.php"   class="nav-link" style="color: #5c9758;">Close Link (Logout)</a>
      </nav>
    </div>
  </header>

  <main id="main-content" class="admin-dashboard">
    
    <h1 class="hero-title" style="font-size: 2rem;">Admin Dashboard</h1>
    <p class="hero-sub" style="margin-bottom: 30px; font-size: 0.9rem;">User Access Level and Product Management</p>

    <?php if (!empty($status_msg) || !empty($error_msg)): ?>
        <div class="system-feedback">
            <?php 
                if(!empty($error_msg)) { echo "❌ <span style='color: #5c9758;'>[ALERT ERR]:</span> " . htmlspecialchars($error_msg); }
                else { echo "✔ <span style='color: #5c9758;'>[STATUS OK]:</span> " . htmlspecialchars($status_msg); }
            ?>
        </div>
    <?php endif; ?>

    <div class="tab-navigation">
        <a href="admin.php?tab=users"  class="tab-btn <?php echo $active_tab === 'users' ? 'tab-btn--active' : ''; ?>">Users Subsystem</a>
        <a href="admin.php?tab=games"  class="tab-btn <?php echo $active_tab === 'games' ? 'tab-btn--active' : ''; ?>">Games Index</a>
        <a href="admin.php?tab=orders" class="tab-btn <?php echo $active_tab === 'orders' ? 'tab-btn--active' : ''; ?>">Global Manifest Logs</a>
    </div>

    <div class="admin-panel">
        
        <?php if ($active_tab === 'users'): ?>
            <h2 style="font-family: var(--font-display); color: var(--accent); margin-top: 0;">Identity Matrix Directory</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Tag Callsign</th>
                        <th>Network Comms Email</th>
                        <th>Access Clearance Role</th>
                        <th>Operational Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users_res = $conn->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
                    while ($user_row = $users_res->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#00{$user_row['id']}</td>";
                        echo "<td>" . htmlspecialchars($user_row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user_row['email']) . "</td>";
                        echo "<td><span style='color:var(--accent2); font-weight:bold;'>{$user_row['role']}</span></td>";
                        echo "<td>
                                <form action='admin.php?tab=users' method='post' class='inline-action-form' onsubmit=\"return confirm('Purge this profile from the registry?');\">
                                    <input type='hidden' name='action' value='delete_user'>
                                    <input type='hidden' name='user_id' value='{$user_row['id']}'>
                                    <button type='submit' class='btn-action-delete'>Purge User</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

        <?php elseif ($active_tab === 'games'): ?>
            <div class="game-manager-grid">
                
                <div>
                    <h3 style="font-family: var(--font-display); color: var(--accent); margin-top: 0;">
                        <?php echo $edit_game['id'] > 0 ? "Modify System Item Module" : "Inject New Catalog Entry"; ?>
                    </h3>
                    <form action="admin.php?tab=games" method="post" style="margin-top: 15px;">
                        <input type="hidden" name="action" value="save_game">
                        <input type="hidden" name="game_id" value="<?php echo $edit_game['id']; ?>">
                        
                        <div class="form-group">
                            <label>Product Title Block:</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($edit_game['title']); ?>" placeholder="e.g., Cyber Horizon" required>
                        </div>
                        <div class="form-group">
                            <label>Classification / Genre Matrix:</label>
                            <input type="text" name="genre" value="<?php echo htmlspecialchars($edit_game['genre']); ?>" placeholder="e.g., RPG / Simulation" required>
                        </div>
                        <div class="form-group">
                            <label>License Metric Cost ($):</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $edit_game['price']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Graphic Asset Token Indicator:</label>
                            <input type="text" name="image_placeholder" value="<?php echo htmlspecialchars($edit_game['image_placeholder']); ?>" placeholder="e.g. 🛸 or ⚔">
                        </div>
                        
                        <button type="submit" class="btn-primary" style="width: 100%; font-size: 0.8rem; padding: 10px;">
                            <?php echo $edit_game['id'] > 0 ? "Commit Configuration Adjustments" : "Compile Software Entry Block"; ?>
                        </button>
                        
                        <?php if($edit_game['id'] > 0): ?>
                            <a href="admin.php?tab=games" class="btn-action-delete" style="display: block; text-align: center; margin-top: 10px; text-decoration: none; padding: 8px 0;">Discard Edits</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div>
                    <h3 style="font-family: var(--font-display); color: var(--accent); margin-top: 0;">Active Catalog Matrix Indices</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Software Module Title</th>
                                <th>Classification</th>
                                <th>Price</th>
                                <th>System Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $games_res = $conn->query("SELECT * FROM games ORDER BY id DESC");
                            while ($game_row = $games_res->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$game_row['image_placeholder']}</td>";
                                echo "<td><strong>" . htmlspecialchars($game_row['title']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($game_row['genre']) . "</td>";
                                echo "<td>$" . number_format($game_row['price'], 2) . "</td>";
                                echo "<td>
                                        <a href='admin.php?tab=games&edit_id={$game_row['id']}' class='btn-action-edit'>Edit</a>
                                        <form action='admin.php?tab=games' method='post' class='inline-action-form' onsubmit=\"return confirm('Confirm complete removal of this game module card from storefront data pipelines?');\">
                                            <input type='hidden' name='action' value='delete_game'>
                                            <input type='hidden' name='game_id' value='{$game_row['id']}'>
                                            <button type='submit' class='btn-action-delete' style='margin-left: 4px;'>Delete</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($active_tab === 'orders'): ?>
            <h2 style="font-family: var(--font-display); color: var(--accent); margin-top: 0;">Global Acquisition Logs Manifest</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Purchaser Operator</th>
                        <th>Designated Module Package</th>
                        <th>Units</th>
                        <th>Aggregated Cost</th>
                        <th>Timestamp Logged</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                     /*
                     * use join query to combine three tables (orders, users, games) to
                     * display a complete order summary with username and game title.
                     * JOIN links related records across tables using foreign keys defined
                     * in novaspark.sql: orders.user_id → users.id, orders.game_id → games.id
                     */
                   
                    $orders_query = "SELECT o.id AS order_id, o.quantity, o.total_price, o.order_date, 
                                            u.username, g.title 
                                     FROM orders o 
                                     JOIN users u ON o.user_id = u.id 
                                     JOIN games g ON o.game_id = g.id 
                                     ORDER BY o.order_date DESC";
                    
                    $orders_res = $conn->query($orders_query);
                    if ($orders_res && $orders_res->num_rows > 0) {
                        while ($order_row = $orders_res->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><code>LOG-REF #00{$order_row['order_id']}</code></td>";
                            echo "<td><strong>" . htmlspecialchars($order_row['username']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($order_row['title']) . "</td>";
                            echo "<td>{$order_row['quantity']}</td>";
                            echo "<td><span style='color: var(--accent2);'>$" . number_format($order_row['total_price'], 2) . "</span></td>";
                            echo "<td>" . date("Y-m-d H:i", strtotime($order_row['order_date'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color: var(--muted); padding: 30px;'>No allocation requisition forms have been written to the data nodes yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
  </main>

  <footer id="site-footer">
    <div class="footer-inner">
      <span class="footer-brand">Novaspark Terminal Framework</span>
      <span class="footer-sep">|</span>
      <span class="footer-note">&copy; 2026 Admin Panel Subsystem Matrix</span>
    </div>
  </footer>

</body>
</html>
<?php $conn->close(); ?>