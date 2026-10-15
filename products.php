<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session so we can check login state for the nav bar
 
// Connect to the database 
$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "novaspark_db";
 
$conn = new mysqli($host, $username, $password, $dbname);
 
if ($conn->connect_error) {
    die("Database Link Fault: " . $conn->connect_error);
}
 // DATABASE QUERY — SELECT all game records ordered by newest first (id DESC).
$sql = "SELECT id, title, genre, price, description, image_url 
        FROM games ORDER BY id DESC";
$catalog_result = $conn->query($sql);
 
// Fallback images used when a game's image_url field is empty in the database
$fallback_images = [
    'https://picsum.photos/seed/stellar/600/400',   
    'https://picsum.photos/seed/circuit/600/400',   
    'https://picsum.photos/seed/nebula/600/400',    
    'https://picsum.photos/seed/voidpro/600/400',   
    'https://picsum.photos/seed/arcadia/600/400',   
    'https://picsum.photos/seed/ironx/600/400',     
    'https://picsum.photos/seed/phantom/600/400',   
    'https://picsum.photos/seed/solaris/600/400',   
];
 
$fallback_index = 0;  // Tracks which fallback image to use next
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Catalogue — Novaspark Games</title>
    
    <link rel="stylesheet" href="Stylesheet.css">
    
    <style>
        .catalog-layout {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .product-card {
            background-color: var(--panel);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, border-color 0.2s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }
 
        .product-card--coming-soon {
            opacity: 0.55;
            pointer-events: none; 
        }
 
        .coming-soon-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--accent2);
            color: var(--header-bg, #0d0d0d);
            font-family: var(--font-mono);
            font-size: 0.65rem;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 2px;
        }
        
        .product-image-wrapper {
            width: 100%;
            height: 200px;
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
            position: relative;
        }
        
        .product-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-card-img {
            transform: scale(1.05); 
        }
        
        .product-details {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .product-meta-genre {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: #5c9758;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        
        .product-card-title {
            font-family: var(--font-display);
            font-size: 1.4rem;
            margin: 0 0 10px 0;
            color: var(--text);
        }
 
        .product-excerpt {
            font-size: 0.82rem;
            color: var(--muted, #888);
            line-height: 1.5;
            margin-bottom: 8px;
            flex-grow: 1;
        }
        
        .product-action-row {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(93, 252, 141, 0.08);
        }
        
        .product-price-tag {
            font-family: var(--font-mono);
            font-size: 1.1rem;
            color: var(--accent2);
            font-weight: bold;
        }
        
        .btn-order-link {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            background-color: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 6px 14px;
            text-decoration: none;
            border-radius: 2px;
            transition: all 0.2s;
        }
        
        .btn-order-link:hover {
            background-color: var(--accent);
            color: var(--header-bg);
        }
 
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
 
  <header id="site-header">
    <div class="header-inner">
      <div class="site-logo">NOVASPARK GAMES</div>
      <nav class="site-nav" aria-label="Main navigation">
        <a href="home_page.html" class="nav-link nav-link--active">Home</a>
        <a href="products.php" class="nav-link nav-link--active">Products</a>
        <a href="About.html"     class="nav-link">About Us</a>
        <a href="contact.php"   class="nav-link">Contact Us</a>
        <a href="search.php"     class="nav-link">Search</a>
        <a href="order.php"      class="nav-link">Order</a>
        <a href="login.php"      class="nav-link">Login</a>
        <a href="links.html"     class="nav-link">Useful Links</a>
        

        <?php  
        
        /*  PHP checks whether a user is logged in and renders different navigation links accordingly.
          * Admins get an additional "Control Room" link.
          */
        if (isset($_SESSION['user_id'])): ?>     

            <a href="my_orders.php" class="nav-link">My Orders</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="nav-link" style="color: var(--accent2);">Control Room</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-link" style="color: #5c9758;">Logout</a>
        <?php else: ?>
            <a href="login.php"    class="nav-link">Login</a>
            <a href="register.php" class="nav-link">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
 
  <main id="main-content" class="catalog-layout">
    <h1 class="hero-title" style="text-align: center;">Our Games</h1>
    <p class="hero-sub" style="text-align: center; margin-bottom: 30px;">
      Every title in our catalogue has been built from the ground up by
      the Novaspark team. Browse below, filter by genre, and click
      <strong>Buy Now</strong> to go to the full product page and checkout.
      All prices are in GBP (£).
    </p>
 
    <div class="divider"><span>Latest Releases</span></div>
 
    <div class="products-grid">
 
      <?php
      $card_count = 0;

      /*  fetch_assoc() retrieves one row at a time as an
       * associative array. The while loop continues until all rows are consumed.
       * Each iteration generates one product card in the HTML output.
       */
 
      if ($catalog_result && $catalog_result->num_rows > 0) {
          while ($game = $catalog_result->fetch_assoc()) {
              $card_count++;
 
              if (!empty($game['image_url'])) {
                  $image_path = htmlspecialchars($game['image_url']);
              } else {
                  $image_path = $fallback_images[$fallback_index % count($fallback_images)];
                  $fallback_index++;
              }
              ?>
              <div class="product-card">
                  
                  <div class="product-image-wrapper">
                      <img
                        src="<?php echo $image_path; ?>"
                        alt="<?php echo htmlspecialchars($game['title']); ?> cover art"
                        class="product-card-img"
                        loading="lazy"
                      >
                  </div>
                  
                  <div class="product-details">
                    <!-- htmlspecialchars() applied to every database value before output -->
                      <span class="product-meta-genre"><?php echo htmlspecialchars($game['genre']); ?></span>
                      <h3 class="product-card-title"><?php echo htmlspecialchars($game['title']); ?></h3>
 
                      
                      <div class="product-action-row">
                          <span class="product-price-tag">
                              £<?php echo number_format($game['price'], 2); ?>
                          </span>
                        <!-- "Buy Now" passes the game's database ID as a URL parameter to order.php -->
                          <a href="order.php?game_id=<?php echo (int)$game['id']; ?>" class="btn-order-link">
                              Buy Now
                          </a>
                      </div>
                  </div>
 
              </div>
              <?php
          } 
      } else {
        // Handle the case where no games exist in the database yet
          echo "<p class='news-placeholder' style='grid-column: 1/-1; text-align: center; color: var(--muted);'>
                  No titles are currently listed. Check back soon.
                </p>";
      }
    ?>

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
<?php
$conn->close(); // Release the database connection once all output is complete
?>