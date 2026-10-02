<?php
$name = htmlspecialchars((string) ($name ?? session()->get('user_name') ?? 'User'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/sss-auth.css') ?>">
</head>
<body>
    <main class="auth-container">
        <section class="auth-form">
            <img class="site-logo" src="<?= base_url('assets/logo-sss.png') ?>" alt="SunSonSolar logo">
            <h1>Welcome, <?= $name ?>!</h1>
            <p>You are logged in to SunSonSolar.</p>
            <a class="button-link" href="<?= site_url('logout') ?>">Log out</a>
        </section>
    </main>
</body>
</html>
