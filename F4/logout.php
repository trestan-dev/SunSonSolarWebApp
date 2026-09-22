<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
session_unset();
session_destroy();
header('Location: login.html');
exit;