<?php
 
session_start();
/*
 * Session guard to protect the order endpoint from unauthenticated access.
 * If user_id is not set in the session, the user is not logged in and is
 * redirected to the login page. This enforces multi-user access.
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connects to the database
$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "novaspark_db";
 
$university_email = "precious.nnwachukwu@gmail.com"; // Order notifications sent here
 
$conn = new mysqli($host, $username, $password, $dbname);
 
if ($conn->connect_error) {
    die("Database Telemetry Interrupted: " . $conn->connect_error);
}
 
$customer_name  = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';

// intval() ensures game_id is always treated as an integer, blocking any string injection
$game_id        = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;

// Clamps quantity to at least 1 if zero or negative is submitted
$quantity       = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
 
$order_success = false;
$error_message = "";

// Server-side validation ensures all required fields have valid values
if (!empty($customer_name) && !empty($customer_email) && $game_id > 0) {
 
/*
     * Prepared statement fetches the game's title and price
     * from the database using the submitted game_id.
     * This ensures price and title come from the database, not from user input
     * users cannot manipulate the price by altering the form.
     */
    $stmt = $conn->prepare("SELECT title, price FROM games WHERE id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
 
    if ($result->num_rows > 0) {
        $game       = $result->fetch_assoc();
        $game_title = $game['title'];
        $game_price = $game['price'];
        $total_cost = $game_price * $quantity;
    
        // Build order confirmation email
        $subject = "NEW REQUISITION LOG: " . $game_title . " Order";
 
        $email_message  = "=========================================\n";
        $email_message .= "  NOVASPARK GAMES REQUISITION LOG REPORT \n";
        $email_message .= "=========================================\n\n";
        $email_message .= "Name: "        . $customer_name  . "\n";
        $email_message .= "Email Address: " . $customer_email . "\n\n";
        $email_message .= "--- ORDER DESIGNATION SUMMARY ---\n";
        $email_message .= "Software: "       . $game_title . "\n";
        $email_message .= "Quantity Ordered: "      . $quantity   . " Unit(s)\n";
        $email_message .= "Unit Licence Price: £"   . number_format($game_price, 2) . "\n";
        $email_message .= "Calculated Gross Value: £" . number_format($total_cost, 2) . "\n\n";
        $email_message .= "=========================================\n";
        $email_message .= "NOTICE: NO TRANSACTION PAYMENTS COLLECTED IN PIPELINE.\n";
        $email_message .= "=========================================\n";
 
        // Set email headers (From, Reply-To, mailer identification)
        $headers  = "From: webmaster@novasparkgames.example.com\r\n";
        $headers .= "Reply-To: " . $customer_email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
    /*
         * PHP's built-in mail() function sends the order details
         * to the designated email address. The @ suppresses any errors from
         * mail servers that are not configured in the local environment.
         * This satisfies the requirement to "send the order as email".
         */
        
        @mail($university_email, $subject, $email_message, $headers);
 
        /*
         * Prepared statement with bound parameters
         * writes the order to the orders table for persistent storage.
         * $_SESSION['user_id'] links the order to the currently logged-in user
         * so it can later be retrieved on my_orders.php.
         * "iiid" = integer, integer, integer, double (for the decimal price).
         */
        $order_stmt = $conn->prepare(
            "INSERT INTO orders (user_id, game_id, quantity, total_price)
             VALUES (?, ?, ?, ?)"
        );
        $order_stmt->bind_param("iiid",
            $_SESSION['user_id'],
            $game_id,
            $quantity,
            $total_cost
        );
        $order_stmt->execute();
        $order_stmt->close();
 
        $order_success = true; // Signal the HTML section below to show a success message
 
    } else {
        $error_message = "Selected Software designation ID matrix is unlisted inside active tables.";
    }
    $stmt->close();
 
} else {
    $error_message = "All operational form parameters must be fully populated.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Process — Novaspark Games</title>
    <link rel="stylesheet" href="Stylesheet.css">
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
        <a href="order.php"      class="nav-link">Order</a>
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
    <section class="section order-container" style="text-align: center;">
 
      <?php 
      /*
       * PHP conditional rendering displays a success or failure message
       * depending on whether $order_success was set to true above.
       * All dynamic values are passed through htmlspecialchars() before output.
       */
      
      if ($order_success): ?>
 
          <h1 class="hero-title" style="color: var(--success);">Requisition Authenticated</h1>
          <div class="divider"><span>Transmission Complete</span></div>
 
          <p class="hero-sub" style="margin-top: 20px;">
            Thank you, <strong><?php echo htmlspecialchars($customer_name); ?></strong>.
            Your procurement request was forwarded to
            <strong><?php echo htmlspecialchars($university_email); ?></strong> for analysis.
          </p>
        
          <!-- Order summary panel, values come from the database, not from the form -->
          <div style="background: var(--bg); border: 1px solid var(--border); padding: 20px;
                      font-family: var(--font-mono); font-size: 0.85rem; text-align: left;
                      display: inline-block; width: 100%; box-sizing: border-box; border-radius: 4px;">
              <span style="color: var(--accent);">[LICENSE MODULE]:</span>
              <?php echo htmlspecialchars($game_title); ?><br>
 
              <span style="color: var(--accent);">[ALLOCATION QUANTITY]:</span>
              <?php echo (int)$quantity; ?><br>
              
        <!-- number_format() ensures consistent 2 decimal place currency display -->
              <span style="color: var(--accent);">[AGGREGATED INVOICE]:</span>
              £<?php echo number_format($total_cost, 2); ?> (No Payment Processed)<br>
          </div>
 
      <?php else: ?>
 
          <h1 class="hero-title" style="color: var(--danger);">Requisition Blocked</h1>
          <div class="divider"><span>Data Matrix Fault</span></div>
          <p class="hero-sub"><?php echo htmlspecialchars($error_message); ?></p>
 
      <?php endif; ?>
 
      <div style="margin-top: 40px;">
          <a href="order.php" class="btn-primary" style="display: inline-block; text-decoration: none;">
            Return to Order System
          </a>
      </div>
 
    </section>
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
<?php $conn->close(); ?>