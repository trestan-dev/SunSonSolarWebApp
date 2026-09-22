<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$name = htmlspecialchars((string)$_SESSION['user_name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-container">
        <section class="auth-form">
            <img class="site-logo" src="assets/logo-sss.png" alt="SunSonSolar logo">
            <h1>Welcome, <?= $name ?>!</h1>
            <p>You are logged in to SunSonSolar.</p>
            <a class="button-link" href="logout.php">Log out</a>
        </section>
    </main>
</body>
</html>