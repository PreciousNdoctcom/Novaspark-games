<?php
/*
 * Site configuration.
 * Copy this file to includes/config.php and fill in your own values.
 * Any value can also be set with an environment variable of the same name
 * (used on hosted demos such as Vercel).
 */

return [
    // 'mysql' for XAMPP / MariaDB. 'sqlite' runs a self-seeding demo database in the temp folder.
    'DB_DRIVER'   => 'mysql',
    'DB_HOST'     => 'localhost',
    'DB_PORT'     => '3306',
    'DB_NAME'     => 'novaspark_db',
    'DB_USER'     => 'root',
    'DB_PASS'     => '',

    // Order and contact emails are copied here. Never shown on screen.
    'STUDIO_EMAIL' => 'studio@novasparkgames.example.com',
    // Public contact address shown in the footer and on the Contact page.
    'PUBLIC_EMAIL' => 'info@novasparkgames.example.com',
    // From address for outgoing mail.
    'MAIL_FROM'    => 'no-reply@novasparkgames.example.com',

    // Leave empty to work it out automatically (works in an XAMPP sub folder).
    'BASE_URL'    => '',

    // true only on your own machine: shows PHP errors on screen.
    'DEBUG'       => false,
];
