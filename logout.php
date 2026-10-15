<?php

session_start(); // Must start the session before it can be destroyed 

// First step is to clear all variables stored in the session array
$_SESSION = array();

/*
 * Step 2 is to delete the session cookie from the user's browser.
 * session_get_cookie_params() retrieves the cookie settings used when
 * the session was created (path, domain, secure, httponly).
 * The same settings are used but set the expiry to the past (time() - 42000)
 * so the browser immediately removes the cookie.
 */

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Step 3 is to destroy the session data stored on the server
session_destroy();

// Redirect to the login page once the session is fully cleared
header("Location: login.php");
exit();
?>