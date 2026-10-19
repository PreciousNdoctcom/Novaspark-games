<?php
/*
 * One shared database connection for the whole request.
 * PDO is used so the same prepared statements run on MySQL / MariaDB (XAMPP)
 * and on SQLite (the self-seeding hosted demo). Errors throw PDOException.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (config('DB_DRIVER') === 'sqlite') {
        $pdo = sqlite_demo_connection($options);
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        config('DB_HOST'),
        config('DB_PORT'),
        config('DB_NAME')
    );
    $pdo = new PDO($dsn, config('DB_USER'), config('DB_PASS'), $options);
    return $pdo;
}

/*
 * Demo database: created and seeded in the temp folder on first use.
 * It resets whenever the host recycles the temp folder.
 */
function sqlite_demo_connection(array $options): PDO
{
    $path = rtrim(sys_get_temp_dir(), '/\\') . '/novaspark-demo-v2.sqlite';

    if (!is_file($path)) {
        $lock = fopen($path . '.lock', 'c');
        flock($lock, LOCK_EX);
        if (!is_file($path)) {
            $tmp = $path . '.' . bin2hex(random_bytes(4));
            $seed = new PDO('sqlite:' . $tmp, null, null, $options);
            $seed->exec(file_get_contents(APP_ROOT . '/database/novaspark.sqlite.sql'));
            $seed->exec(file_get_contents(APP_ROOT . '/database/seed.sql'));
            $seed = null;
            rename($tmp, $path);
        }
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, $options);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 3000');
    return $pdo;
}

/* Run a prepared statement and return it. */
function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
