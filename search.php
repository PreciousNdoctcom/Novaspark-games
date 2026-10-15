<?php

session_start();
 
// Connect to Database
$host     = "localhost";
$username = "root";
$password = "";
$dbname   = "novaspark_db";
 
$conn = new mysqli($host, $username, $password, $dbname);
 
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
 
/*
 * Use Dropdown to populate dynamically from DISTINCT genre values in the database.
 * Allows new genres to appear automatically when games are added via the admin panel,
 * without any changes to this file.
 */

$genre_options = [];
$genre_result  = $conn->query("SELECT DISTINCT genre FROM games ORDER BY genre ASC");
if ($genre_result) {
    while ($g = $genre_result->fetch_assoc()) {
        $genre_options[] = $g['genre'];
    }
}
 
// Variables will be populated if a search was submitted
$search_term    = "";
$selected_genre = "ALL";
$selected_sort  = "title";   
$results        = [];
$searched       = false;
 
// Only run a database query if the 'query' parameter exists in the URL (GET request)
if (isset($_GET['query'])) {
    $searched       = true;
    $search_term    = trim($_GET['query']);
    $selected_genre = isset($_GET['genre']) ? $_GET['genre'] : 'ALL';
 
    /*
     * The ORDER BY clause cannot use a prepared statement placeholder,
     * so user input must be validated against a fixed list of allowed values.
     * Any value not in $allowed_sorts defaults to 'title', preventing SQL injection
     * via a manipulated sort parameter.
     */
    
    $allowed_sorts  = [
        'title'      => 'title ASC',
        'price_asc'  => 'price ASC',
        'price_desc' => 'price DESC',
    ];
    $selected_sort  = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowed_sorts)
                      ? $_GET['sort']
                      : 'title';
    $order_clause   = $allowed_sorts[$selected_sort];
/*
     * Wrapping the search term in % wildcards allows partial matching.
     * e.g. searching "void" will match "Void Protocol".
     */
 
    $like_term = "%" . $search_term . "%";
 
     /*
     * Two different queries are prepared depending
     * on whether a genre filter has been selected. Both use bound parameters (?)
     * to safely inject user-supplied values into the SQL.
     */

    if ($selected_genre === "ALL") {
      // Search title AND description across all genres
        $sql  = "SELECT * FROM games
                  WHERE title LIKE ? OR description LIKE ?
                  ORDER BY $order_clause";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $like_term, $like_term);
    } else {
      // Same search but also constrained to one specific genre
        $sql  = "SELECT * FROM games
                  WHERE (title LIKE ? OR description LIKE ?) AND genre = ?
                  ORDER BY $order_clause";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $like_term, $like_term, $selected_genre);
    }
 
    $stmt->execute();
    $db_result = $stmt->get_result();
 
    // Collect all matching rows into an array for use in the HTML section below
    while ($row = $db_result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
 
// Build the page title dynamically to reflect the current search term
$page_title = $search_term
    ? htmlspecialchars($search_term) . ' — Search'
    : 'Search the Catalogue';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <title><?php echo $page_title; ?> — Novaspark Games</title>
 
    <link rel="stylesheet" href="Stylesheet.css">
 
    <style>
    
        .search-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 6px;
        }
 
        .search-controls input[type="text"] {
            flex: 1 1 260px;
        }
 
        .search-controls select,
        .search-controls button {
            flex: 0 0 auto;
        }
 
     
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
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
 
        .product-image-wrapper {
            width: 100%;
            height: 200px;
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
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
            font-size: 1.3rem;
            margin: 0 0 6px 0;
            color: var(--text);
        }
 
        .product-release {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: var(--muted, #888);
            margin-bottom: 10px;
        }
 
        .product-excerpt {
            font-size: 0.82rem;
            color: var(--muted, #888);
            line-height: 1.5;
            flex-grow: 1;
            margin-bottom: 8px;
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
 

        .results-meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 40px;
        }
 
        .search-result-count {
            font-family: var(--font-mono);
            font-size: 0.85rem;
            color: var(--muted, #888);
        }
 
        .search-result-count strong {
            color: var(--accent2);
        }
 
        .no-results-panel {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted, #888);
        }
 
        .no-results-panel a {
            color: var(--accent);
        }
 
        @media (max-width: 480px) {
            .results-grid { grid-template-columns: 1fr; }
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
        <a href="search.php"     class="nav-link nav-link--active">Search</a>
        <a href="order.php"      class="nav-link">Order</a>
        <a href="links.html"     class="nav-link">Useful Links</a>
 
        <?php if (isset($_SESSION['user_id'])): ?>
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
 
  <main id="main-content">
    <section class="section search-container">
 
      <h1 class="hero-title" style="text-align: center;">Search Our Catalogue</h1>
     
      <p class="hero-sub" style="text-align: center; margin-bottom: 30px;">
        Looking for something specific? Search by title or description and
        narrow results by genre. All prices are in GBP (£).
      </p>

      <!--
        GET method used here (not POST) so search terms appear in the URL.
        This allows results pages to be shared or bookmarked.
      -->

      <form action="search.php" method="get" id="search-form" onsubmit="return verifySearchInput(event)">
        <div class="search-controls">

      <!-- value= repopulates the search box with the previous query -->
          <input
            type="text"
            id="search-query"
            name="query"
            placeholder="Enter a title or keyword…"
            value="<?php echo htmlspecialchars($search_term); ?>"
          >
      
          <!-- Genre dropdown options are generated from the database query above -->
          <select id="search-genre" name="genre">
            <option value="ALL" <?php if ($selected_genre === 'ALL') echo 'selected'; ?>>All Genres</option>
            <?php foreach ($genre_options as $genre): ?>
              <option value="<?php echo htmlspecialchars($genre); ?>"
                <?php if ($selected_genre === $genre) echo 'selected'; ?>>
                <?php echo htmlspecialchars($genre); ?>
              </option>
            <?php endforeach; ?>
          </select>
 
          <!-- Sort options are validated server-side against the whitelist in $allowed_sorts -->
          <select id="search-sort" name="sort">
            <option value="title"      <?php if ($selected_sort === 'title')      echo 'selected'; ?>>A – Z</option>
            <option value="price_asc"  <?php if ($selected_sort === 'price_asc')  echo 'selected'; ?>>Price: Low to High</option>
            <option value="price_desc" <?php if ($selected_sort === 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
          </select>
 
          <button type="submit" class="btn-primary">Search</button>
        </div>
        
        <!-- Error message area updated by verifySearchInput() in script.js -->
         <p id="search-error-msg" class="form-message" style="margin-top: 8px;"></p>
      </form>
 
      <div class="results-wrapper">
        <?php // Only render results section if a search has been performed
        if ($searched): ?>
        
          <div class="results-meta-bar">
            <p class="search-result-count">
              <?php if ($search_term !== ''): ?>
                <!-- Display result count and the sanitised search term back to the user -->
                <strong><?php echo count($results); ?></strong>
                result<?php echo count($results) !== 1 ? 's' : ''; ?>
                for "<strong><?php echo htmlspecialchars($search_term); ?></strong>"
                <?php if ($selected_genre !== 'ALL'): ?>
                  in <strong><?php echo htmlspecialchars($selected_genre); ?></strong>
                <?php endif; ?>
              <?php else: ?>
                <strong><?php echo count($results); ?></strong> game<?php echo count($results) !== 1 ? 's' : ''; ?> found
              <?php endif; ?>
            </p>
          </div>
 
          <div class="divider"><span>Results</span></div>
 
          <?php if (count($results) > 0): ?>
 
            <div class="results-grid">
              <?php foreach ($results as $game): ?>
 
                <?php
                // Image fallback: use the stored URL or generate one from the title
                  $img = !empty($game['image_url'])
                       ? htmlspecialchars($game['image_url'])
                       : 'https://picsum.photos/seed/' . urlencode($game['title']) . '/600/400';
                ?>
 
                <div class="product-card">
 
                  <div class="product-image-wrapper">
                    <img
                      src="<?php echo $img; ?>"
                      alt="<?php echo htmlspecialchars($game['title']); ?> cover art"
                      class="product-card-img"
                      loading="lazy"
                    >
                  </div>
 
                  <div class="product-details">
                    <!-- All database values are passed through htmlspecialchars() before output -->
                    <span class="product-meta-genre"><?php echo htmlspecialchars($game['genre']); ?></span>
                    <h3 class="product-card-title"><?php echo htmlspecialchars($game['title']); ?></h3>
 
                    <?php if (!empty($game['release_year'])): ?>

                      <p class="product-release">Released: <?php echo (int)$game['release_year']; ?></p>
                    <?php endif; ?>
 
                    <p class="product-excerpt"><?php echo htmlspecialchars($game['description']); ?></p>
 
                    <div class="product-action-row">
                    
                      <span class="product-price-tag">
                        £<?php echo number_format($game['price'], 2); ?>
                      </span>
 
                      <a href="order.php?game_id=<?php echo (int)$game['id']; ?>" class="btn-order-link">
                        Buy Now
                      </a>
                    </div>
                  </div>
 
                </div>
 
              <?php endforeach; ?>
            </div>
 
          <?php else: ?>
          <!-- Friendly empty state when no games match the search criteria -->
            <div class="no-results-panel">
              <p>No games found for that search.</p>
              <p style="margin-top: 10px;">
                Try a different keyword or genre, or
                <a href="products.php">browse the full catalogue →</a>
              </p>
            </div>
 
          <?php endif; ?>
 
        <?php endif; ?>
      </div>
 
    </section>
  </main>
 
  <footer id="site-footer">
    <div class="footer-inner">
      <span class="footer-brand">Novaspark Games</span>
      <span class="footer-sep">|</span>
      <a href="mailto:info@novasparkgames.example.com" class="footer-link">
        info@novasparkgames.example.com
      </a>
      <span class="footer-sep">|</span>
      <span class="footer-note">&copy; 2026 Novaspark Games — All prices in GBP</span>
    </div>
  </footer>
 
  <script src="script.js"></script>
</body>
</html>
<?php $conn->close(); ?>