<?php
/* Old URL: permanently moved. */
require __DIR__ . '/includes/helpers.php';
$id = (int) ($_GET['game_id'] ?? 0);
header('Location: ' . url($id > 0 ? 'checkout.php?game_id=' . $id : 'games.php'), true, 301);
