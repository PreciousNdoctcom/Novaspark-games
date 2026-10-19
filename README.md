# Novaspark Games (version 2 redesign)

A game storefront built for an MSc Computing module (University of Sunderland).
Customers register, log in, browse, search, filter by genre and place simulated
orders; admins manage games and users and see every order. **No payment is ever taken.**

## Tech stack
PHP 8, MySQL / MariaDB (PDO with prepared statements), HTML, CSS and vanilla JavaScript.
No frameworks, no build step, no npm.

## Run it on XAMPP
1. Copy this folder into `htdocs` (for example `htdocs/novaspark`).
2. Start Apache and MySQL in the XAMPP control panel.
3. In phpMyAdmin, import `database/novaspark.sql`.
   Already have the version 1 database? Import `database/migrate-v1-to-v2.sql` instead to keep your data.
4. Copy `includes/config.example.php` to `includes/config.php` and check the database details
   (XAMPP defaults: user `root`, empty password).
5. Open http://localhost/novaspark/ (or `/entrance.php` for the academic entrance page).

Demo accounts: `admin` / `admin` and `customer1` / `customer1`.

Email uses PHP `mail()`. On a default XAMPP install it usually can't send; orders and
messages are still saved and the failure is logged.

## Hosted demo (Vercel)
`vercel.json` and `api/index.php` run the same pages on Vercel's PHP runtime.
With no `DB_HOST` set, the demo uses a self-seeding SQLite database in the server's
temp folder, so data resets from time to time. Set `DB_DRIVER=mysql` plus `DB_HOST`,
`DB_PORT`, `DB_NAME`, `DB_USER` and `DB_PASS` environment variables to use a real MySQL database.

## Structure
- `index.php`, `games.php`, `game.php`, `checkout.php`, `order-confirmation.php`, `my-orders.php`,
  `about.php`, `contact.php`, `links.php`, `entrance.php`, `login.php`, `register.php`, `logout.php`, `404.php`
- `admin/index.php`: Users, Games and Orders tabs
- `includes/`: config, database connection, helpers, shared header and footer, components
- `assets/css/tokens.css`: every colour, font and size token; other stylesheets use tokens only
- `assets/js/app.js`: all behaviour, attached through `data-` attributes
- Old URLs (`products.php`, `search.php`, `order.php`, `Home_Page.html` and so on) redirect with 301.

## Security
Prepared statements, `password_hash` / `password_verify`, `htmlspecialchars` on all output,
one CSRF token per session on every form, `?next=` limited to paths on this site,
and `includes/` and `database/` blocked from the web.

## Author
Precious Nwachukwu · [LinkedIn](https://www.linkedin.com/in/preciousnnwachukwu/)
