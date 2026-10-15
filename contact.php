<?php

session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — Novaspark Games</title>
 
    <link rel="stylesheet" href="Stylesheet.css">
 
    <style>
 
      .contact-card {
        max-width: 700px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(144, 238, 100, 0.18);
        border-radius: 12px;
        padding: 2.5rem 2.5rem 2rem;
      }
 
      .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem 1.5rem;
      }
 
      .form-grid .full {
        grid-column: 1 / -1;
      }
 
      .form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
      }
 
      .field-label {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(144, 238, 100, 0.8);
      }
 
      .field-label .req {
        color: #ff6b6b;
        margin-left: 2px;
      }
 
      .form-grid input,
      .form-grid select,
      .form-grid textarea {
        width: 100%;
        background: rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(144, 238, 100, 0.22);
        border-radius: 7px;
        padding: 10px 13px;
        font-size: 14px;
        color: #e8f5e0;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
      }
 
      .form-grid input::placeholder,
      .form-grid textarea::placeholder {
        color: rgba(200, 230, 180, 0.35);
      }
 
      .form-grid select option {
        background: #0d2010;
        color: #e8f5e0;
      }
 
      .form-grid select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2390ee64' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 34px;
        cursor: pointer;
      }
 
      .form-grid textarea {
        resize: vertical;
        min-height: 130px;
        line-height: 1.6;
      }
 
      .form-grid input:focus,
      .form-grid select:focus,
      .form-grid textarea:focus {
        border-color: rgba(144, 238, 100, 0.7);
        box-shadow: 0 0 0 3px rgba(144, 238, 100, 0.12);
      }
 
      .form-message {
        font-size: 13px;
        min-height: 1.2em;
        margin: 0;
        color: #ff6b6b;
      }
 
      .required-note {
        font-size: 12px;
        color: rgba(200, 230, 180, 0.45);
        text-align: right;
        margin-top: -0.25rem;
      }
 
      .form-divider {
        border: none;
        border-top: 1px solid rgba(144, 238, 100, 0.1);
        margin: 0.25rem 0;
        grid-column: 1 / -1;
      }
 
      .btn-row {
        display: flex;
        justify-content: center;
        margin-top: 0.5rem;
      }
 
      .btn-row .btn-primary {
        width: 100%;
        max-width: 320px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 2rem;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
      }
 
      @media (max-width: 560px) {
        .contact-card {
          padding: 1.5rem 1.25rem;
        }
 
        .form-grid {
          grid-template-columns: 1fr;
        }
 
        .form-grid .full {
          grid-column: 1;
        }
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
        <a href="contact.php"    class="nav-link nav-link--active">Contact Us</a>
        <a href="search.php"     class="nav-link">Search</a>
        <a href="order.php"      class="nav-link">Order</a>
        <a href="login.php"      class="nav-link">Login</a>
        <a href="links.html"     class="nav-link">Useful Links</a>
      </nav>
    </div>
  </header>
 
  <main id="main-content">
    <section class="section form-container">
 
      <h1 class="hero-title" style="text-align: center; margin-bottom: 10px;">Get In Touch</h1>
      <p class="hero-sub" style="text-align: center; margin-bottom: 36px;">
        Whether you have a question about an order, feedback on one of our
        games, or a press enquiry, we'd love to hear from you. Fill in the
        form below and we'll aim to respond within two working days.
      </p>
 
      <div class="contact-card">
 
        <form action="handle_form.php" method="post" id="contact-grid"
              onsubmit="return validateContactForm(event)">
 
          <input type="hidden" name="csrf_token"
                 value="<?php echo $_SESSION['csrf_token']; ?>">
 
          <div class="form-grid">
 
            <div class="form-group">
              <label for="contact-name" class="field-label">
                Name <span class="req">*</span>
              </label>
              <input type="text"
                     id="contact-name"
                     name="name"
                     placeholder="First and last name"
                     autocomplete="name">
            </div>
 
            <div class="form-group">
              <label for="contact-email" class="field-label">
                Email <span class="req">*</span>
              </label>
              <input type="email"
                     id="contact-email"
                     name="email"
                     placeholder="name@domain.com"
                     autocomplete="email">
            </div>
 
            <div class="form-group">
              <label for="contact-title" class="field-label">
                Title <span class="req">*</span>
              </label>
              <select id="contact-title" name="gender">
                <option value="">-- Select Title --</option>
                <option value="M">Mr</option>
                <option value="F">Ms</option>
                <option value="Mrs">Mrs</option>
                <option value="Dr">Dr</option>
                <option value="Other">Other</option>
              </select>
            </div>
 
            <div class="form-group">
              <label for="contact-age" class="field-label">
                Age Range <span class="req">*</span>
              </label>
              <select id="contact-age" name="age">
                <option value="">-- Select Age Range --</option>
                <option value="0-29">Under 30</option>
                <option value="30-60">Between 30 and 60</option>
                <option value="60+">Over 60</option>
              </select>
            </div>
 
            <hr class="form-divider">
 
            <div class="form-group full">
              <label for="contact-subject" class="field-label">
                Subject <span class="req">*</span>
              </label>
              <input type="text"
                     id="contact-subject"
                     name="subject"
                     placeholder="What is your message about?">
            </div>
 
            <div class="form-group full">
              <label for="contact-comments" class="field-label">
                Message <span class="req">*</span>
              </label>
              <textarea id="contact-comments"
                        name="comments"
                        rows="5"
                        placeholder="Type your message here..."></textarea>
            </div>
 
            <p class="required-note full">
              <span style="color:#ff6b6b;">*</span> All fields are required
            </p>
 
            <p id="validation-feedback" class="form-message full"></p>
 
            <div class="btn-row full">
              <button type="submit" class="btn-primary">
                Submit
              </button>
            </div>
 
          </div>
 
        </form>
 
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
      <span class="footer-note">&copy; 2026 Novaspark Games</span>
    </div>
  </footer>
 
  <script src="script.js"></script>
 
</body>
</html>