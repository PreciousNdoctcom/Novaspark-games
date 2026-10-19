<?php
/* Old URL: permanently moved. */
require __DIR__ . '/includes/helpers.php';
$p = array_filter(['q' => $_GET['query'] ?? ($_GET['q'] ?? ''), 'genre' => $_GET['genre'] ?? '', 'sort' => $_GET['sort'] ?? ''], 'strlen');
header('Location: ' . url('games.php' . ($p ? '?' . http_build_query($p) : '')), true, 301);
