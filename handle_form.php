<?php

session_start(); // Resume the session to access the CSRF token stored in contact.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Novaspark Games</title>
    <link rel="stylesheet" href="Stylesheet.css">
</head>
<body>
 
  <header id="site-header">
    <div class="header-inner">
      <div class="site-logo">NOVASPARK GAMES</div>
 
      <nav class="site-nav" aria-label="Main navigation">
        <a href="Home_Page.html" class="nav-link">Home</a>
        <a href="products.php"   class="nav-link">Products</a>
        <a href="About.html"     class="nav-link">About Us</a>
        <a href="contact.php"    class="nav-link">Contact Us</a>
        <a href="search.php"     class="nav-link">Search</a>
        <a href="order.php"      class="nav-link">Order</a>
        <a href="links.html"     class="nav-link">Useful Links</a>
        <a href="login.php"      class="nav-link">Login</a>
      </nav>
    </div>
  </header>
 
  <main id="main-content">
    <section class="section form-container">
 
      <?php
      /*
       * METHOD check only accept POST requests.
       * A GET request (e.g. someone typing this URL directly) is redirected
       * back to the contact form. This prevents the processing logic running
       * without any submitted data.
       */
 
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          header('Location: contact.php');
          exit;
      }
  /*
       * contact.php generates a random token and stores it in both the session
       * and a hidden form field. The two values are compared using
       * hash_equals() (timing-safe comparison) to confirm the POST came from
       * our own form, not from a malicious third-party site.
       */
      if (
          empty($_POST['csrf_token']) ||
          !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
      ) {
          echo "<h1 class='hero-title'>Invalid Request</h1>";
          echo "<p class='hero-sub'>Your session may have expired. Please try submitting the form again.</p>";
          echo "<a href='contact.php' class='btn-primary'>Return to form</a>";
          exit;
      }

      // Invalidate the token after a single use to prevent replay attacks
      unset($_SESSION['csrf_token']);
 
      // The null-coalescing operator (??) provides an empty string fallback
      // if a field was not submitted at all.
      $raw_name     = trim($_POST['name']     ?? '');
      $raw_email    = trim($_POST['email']    ?? '');
      $raw_subject  = trim($_POST['subject']  ?? '');
      $raw_gender   = trim($_POST['gender']   ?? '');
      $raw_age      = trim($_POST['age']      ?? '');
      $raw_comments = trim($_POST['comments'] ?? '');

      /*
       * htmlspecialchars() converts characters like
       * < > " & into safe HTML entities before they are echoed to the browser.
       * This prevents XSS attacks where a user might inject malicious scripts.
       * ENT_QUOTES encodes both single and double quotes.
       */
      $name     = htmlspecialchars($raw_name,     ENT_QUOTES, 'UTF-8');
      $email    = htmlspecialchars($raw_email,    ENT_QUOTES, 'UTF-8');
      $subject  = htmlspecialchars($raw_subject,  ENT_QUOTES, 'UTF-8');
      $gender   = htmlspecialchars($raw_gender,   ENT_QUOTES, 'UTF-8');
      $age      = htmlspecialchars($raw_age,      ENT_QUOTES, 'UTF-8');
      $comments = htmlspecialchars($raw_comments, ENT_QUOTES, 'UTF-8');
 /*
       * filter_var() with FILTER_VALIDATE_EMAIL
       * checks that the submitted email matches a valid email format.
       * This runs on the server even though JavaScript also validates the form,
       * because client-side checks can be bypassed.
       */
      if (!filter_var($raw_email, FILTER_VALIDATE_EMAIL)) {
          echo "<h1 class='hero-title'>Invalid Email</h1>";
          echo "<p class='hero-sub'>The email address you entered is not valid.</p>";
          echo "<a href='contact.php' class='btn-primary'>Return to form</a>";
          exit;
      }
  
      // SERVER-SIDE COMPLETENESS CHECK — verify all fields have values
      if (
          !empty($raw_name)    &&
          !empty($raw_email)   &&
          !empty($raw_subject) &&
          !empty($raw_gender)  &&
          !empty($raw_age)     &&
          !empty($raw_comments)
      ) {
 
      // Build the email subject line
          $mail_subject = !empty($raw_subject)
              ? 'Contact Form: ' . $raw_subject
              : 'Contact Form: (No subject)';
 
      // Build the plain-text email body from the submitted fields
          $body =
              "Name:    $raw_name\r\n"  .
              "Email:   $raw_email\r\n" .
              "Age:     $raw_age\r\n"   .
              "Subject: $raw_subject\r\n\r\n" .
              "Message:\r\n$raw_comments";
 
        /*
           * Email headers set the From, Reply-To, and Content-Type headers.
           * Reply-To is the customer's email so replies go directly to them.
           * \r\n is the required line separator for email headers (RFC 2822).
        */
          $headers =
              'From: noreply@novasparkgames.example.com' . "\r\n" .
              'Reply-To: ' . $raw_email                  . "\r\n" .
              'X-Mailer: PHP/' . phpversion()            . "\r\n" .
              'Content-Type: text/plain; charset=UTF-8';
 
          /*
           * PHP mail() sends the contact form content to the specified address.
           * In a production system this would use a library like PHPMailer for
           * reliable SMTP delivery; mail() relies on the server's local MTA.
           */
          
          mail('precious.nnwachukwu@gmail.com', $mail_subject, $body, $headers);
 
          // Build a polite personalised greeting based on the selected title
          if      ($gender === 'M')   $greeting = "Mr. $name";
          elseif  ($gender === 'F')   $greeting = "Ms. $name";
          elseif  ($gender === 'Mrs') $greeting = "Mrs. $name";
          elseif  ($gender === 'Dr')  $greeting = "Dr. $name";
          else                        $greeting = $name;
 
          // Render the confirmation message using the sanitised variables
          echo "<h1 class='hero-title'>Submission Confirmed</h1>";
          echo "<p class='hero-sub'>Thank you, <strong>$greeting</strong>. Your message has been sent.</p>";
 
      } else {
 
      // Idf one or more required fields were empty the user is sent back
          echo "<h1 class='hero-title'>Error</h1>";
          echo "<p class='hero-sub'>Please go back and complete all fields.</p>";
          echo "<a href='contact.php' class='btn-primary'>Return to form</a>";
 
      }
      ?>
 
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
      <span class="footer-note">&copy; 2026 Novaspark Games</span>
    </div>
  </footer>
 
</body>
</html>
 