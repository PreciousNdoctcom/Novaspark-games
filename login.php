<?php
/*
 * Handles user authentication using PHP sessions and MySQL.
 */

session_start(); // Initialise the session

// Redirect already-logged-in users away from the login page
if (isset($_SESSION['user_id'])) {
    header("Location: Home_Page.html");
    exit();
}

// Connect to Database. Credentials are stored as variables to make them easy to update
$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "novaspark_db";

// MySQLi object-oriented connection to the MySQL database
$conn = new mysqli($host, $username, $password, $dbname);

// checks whether the connection succeeded before running any queries
if ($conn->connect_error) {
    die("Database Infrastructure Connection Offline: " . $conn->connect_error);
}

$error_message = "";

// Form processing
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_input = trim($_POST['username']);
    $pass_input = $_POST['password'];

 // Server-side validation: ensure neither field was left empty   
    if (empty($user_input) || empty($pass_input)) {
        $error_message = "Identity validation failure: All access inputs required.";
    } else {

    // prepared statement to prevent SQL injection
        $stmt = $conn->prepare(
            "SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $user_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            
            /*
             * password_verify() compares the plain-text
             * input against the bcrypt hash stored in the database.
             * Passwords are NEVER stored or compared as plain text.
             */
            if (password_verify($pass_input, $user_data['password'])) {

            /*
                 * once logged in store user identity in the session.
                 * Session data is stored server-side; only a session ID cookie
                 * is sent to the browser. This persists across page loads.
                 */
                
                $_SESSION['user_id']  = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role']     = $user_data['role']; 

                // Redirect to products page after login
                header("Location: products.php");
                exit();
                
            } else {
                // Generic error: deliberately does not reveal whether username or password was wrong
                $error_message = "Authentication Denied: Invalid security clearance parameters.";
            }
        } else {
            // No user found — same generic message (prevents username enumeration)
            $error_message = "Authentication Denied: Invalid security clearance parameters.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Initialization — Novaspark Games</title>
    
    <link rel="stylesheet" href="Stylesheet.css">
    
    <style>

        .login-container {
            max-width: 450px;
            margin: 60px auto;
            background-color: var(--panel);
            border: 1px solid var(--border);
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        }
        .form-group {
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: #5c9758; 
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .form-group input {
            background-color: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            font-family: var(--font-mono);
            font-size: 0.85rem;
            padding: 12px;
            border-radius: 2px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: var(--accent);
        }
        .status-box {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            padding: 12px;
            margin-bottom: 24px;
            border-radius: 2px;
            text-align: center;
            background-color: rgba(20, 48, 28, 0.6);
            border: 1px solid #5c9758; 
            color: var(--text);
        }
        .switch-prompt {
            text-align: center;
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 24px;
        }
        .switch-prompt a {
            color: var(--accent);
            text-decoration: none;
        }
        .switch-prompt a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

  <header id="site-header">
    <div class="header-inner">
      <div class="site-logo">NOVASPARK GAMES</div>
      <nav class="site-nav" aria-label="Main navigation">
        <a href="Home_Page.html"      class="nav-link">Home</a>
        <a href="products.php"   class="nav-link">Products</a>
        <a href="About.html"     class="nav-link">About Us</a>
        <a href="contact.php"   class="nav-link">Contact Us</a>
        <a href="login.php"      class="nav-link nav-link--active">Login</a>
      </nav>
    </div>
  </header>

  <main id="main-content">
    <section class="section">
      <div class="login-container">
        <h1 class="hero-title" style="font-size: 1.8rem; text-align: center; margin-bottom: 10px;">Login</h1>
        <div class="divider" style="margin-bottom: 30px;"><span>Verify User</span></div>

        <?php if (!empty($error_message)): ?>
            <div class="status-box">
                ⚠ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
         
        <!--
          The form POSTs to itself (PHP_SELF). htmlspecialchars() is applied
          to the action URL to prevent header injection attacks.
        -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
          
          <div class="form-group">
            <label id="lbl-log-user" for="log-username">Username:</label>
            <input type="text" id="log-username" name="username" placeholder="" autocomplete="off" required>
          </div>

          <div class="form-group">
            <label id="lbl-log-pass" for="log-password">Password:</label>
            <input type="password" id="log-password" name="password" placeholder="" required>
          </div>

          <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
              Login
          </button>

          <p class="switch-prompt">
              First time? <a href="register.php">Create new account</a>
          </p>
        </form>
      </div>
    </section>
  </main>

  <footer id="site-footer">
    <div class="footer-inner">
      <span class="footer-brand">Novaspark Games</span>
      <span class="footer-sep">|</span>
      <span class="footer-note">&copy; 2026 Novaspark Games</span>
    </div>
  </footer>

</body>
</html>
<?php $conn->close(); // database connection closed when finished ?> 