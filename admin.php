<?php
/* Old URL: permanently moved. */
require __DIR__ . '/includes/helpers.php';
$tab = in_array($_GET['tab'] ?? '', ['users', 'games', 'orders'], true) ? $_GET['tab'] : 'users';
header('Location: ' . url('admin/index.php?tab=' . $tab), true, 301);
