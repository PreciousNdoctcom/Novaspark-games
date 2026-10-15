<?php

session_start(); // required before accessing or setting $_SESSION

// Redirect users who are already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: Home_Page.html");
    exit();
}

// Connect to database
$host     = "localhost";
$username = "root";
$password = ""; 
$dbname   = "novaspark_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Core Database Offline: " . $conn->connect_error);
}


$error_message = "";
$success_message = "";

// Registration form processing
if ($_SERVER["REQUEST_METHOD"] === "POST") {

// Trim accidental whitespace
    $user_name = trim($_POST['username']);
    $user_email = trim($_POST['email']);
    $pass_input = $_POST['password'];
    $pass_conf  = $_POST['confirm_password'];

    // Server side validation
    if (empty($user_name) || empty($user_email) || empty($pass_input) || empty($pass_conf)) {
        $error_message = "Halt: All registration matrices must be filled out.";

    // Validate email format using PHP's built-in filter function
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid network address format provided.";
     
    // Confirm the two password fields match 
    } elseif ($pass_input !== $pass_conf) {
        $error_message = "Security Mismatch: Passwords do not match.";
    
    // Enforce a minimum password length
    } elseif (strlen($pass_input) < 6) {
        $error_message = "Security alert: Password length must be at least 6 characters.";
    } else {

    /*
         * Prepared statement queries the users table
         * to see if the email is already registered before attempting INSERT.
         */

        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $user_email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error_message = "Email address already registered.";
        } else {
        
        // Duplicate username check
            $user_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $user_check->bind_param("s", $user_name);
            $user_check->execute();
            if ($user_check->get_result()->num_rows > 0) {
                $error_message = "Username already taken. Please choose another.";
            } else {

             /*
                 * Password_hash() applies bcrypt hashing.
                 * The resulting hash is what gets stored in the database.
                 */
                $hashed_password = password_hash($pass_input, PASSWORD_DEFAULT);

                /*
                 * Insert new user, role defaults to defined role in the database schema 
                 */
                $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $user_name, $user_email, $hashed_password);

                if ($insert_stmt->execute()) {
                // Redirect to login page with a registered=1 flag for a success message
                header("Location: login.php?registered=1");
                exit();
                } else {
                    $error_message = "System error inserting record.";
                }
                $insert_stmt->close();
            }
            $user_check->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup — Novaspark Games</title>
    
    <link rel="stylesheet" href="Stylesheet.css">
    
    <style>
        
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background-color: var(--panel);
            border: 1px solid var(--border);
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .form-group {
            margin-bottom: 20px;
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
            margin-bottom: 20px;
            border-radius: 2px;
            text-align: center;
        }
        .status-box--error {
            background-color: rgba(20, 48, 28, 0.6);
            border: 1px solid #5c9758; 
            color: var(--text);
        }
        .status-box--success {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        .switch-prompt {
            text-align: center;
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 20px;
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
        <a href="register.php"   class="nav-link nav-link--active">Register</a>
      </nav>
    </div>
  </header>

  <main id="main-content">
    <section class="section">
      <div class="register-container">
        <h1 class="hero-title" style="font-size: 1.8rem; text-align: center; margin-bottom: 10px;">Register</h1>
        <div class="divider" style="margin-bottom: 25px;"><span>Create New Account</span></div>

        <?php 
        // Display error or success messages passed back from server-side validation
        if (!empty($error_message)): ?>
            <div class="status-box status-box--error">
                ⚠ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="status-box status-box--success">
                ✔ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
            <!--
          Post method sends data in the request body, not the URL.
        -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
          
          <div class="form-group">
            <label id="lbl-reg-name" for="reg-username">Username:</label>
            <!-- Repopulate the username field so users don't have to retype it after an error -->
            <input type="text" id="reg-username" name="username" placeholder="" value="<?php echo isset($user_name) ? htmlspecialchars($user_name) : ''; ?>">
          </div>

          <div class="form-group">
            <label id="lbl-reg-email" for="reg-email">Email:</label>
            <input type="email" id="reg-email" name="email" placeholder="comms@domain.com" value="<?php echo isset($user_email) ? htmlspecialchars($user_email) : ''; ?>">
          </div>

          <div class="form-group">
            <label id="lbl-reg-pass" for="reg-password">Password:</label>
            <input type="password" id="reg-password" name="password" placeholder="Min. 6 alphanumeric characters">
          </div>

          <div class="form-group">
            <label id="lbl-reg-conf" for="reg-confirm">Confirm Password:</label>
            <input type="password" id="reg-confirm" name="confirm_password" placeholder="">
          </div>

          <button type="submit" class="btn-primary" style="width: 100%; margin-top: 15px;">
              Submit
          </button>

          <p class="switch-prompt">
              Already registered? <a href="login.php">Login</a>
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
<?php $conn->close(); // Release the database connection ?>