<?php
/* Log out (POST with CSRF token from the account menu). */
require __DIR__ . '/includes/helpers.php';

if (is_post() && csrf_check()) {
    // Drop everything in the session and move to a fresh session ID.
    $_SESSION = [];
    session_regenerate_id(true);
    flash('logged_out', true);
}
redirect('index.php');
