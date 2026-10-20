<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/sss-auth.css?v=20261020-back-home') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script type="module" src="<?= base_url('assets/js/sss-auth.js?v=20261020-login-errors') ?>"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form login-form">
            <img class="site-logo" src="<?= base_url('assets/logo-sss.png') ?>" alt="SunSonSolar logo">
            <h1>Welcome to SunSonSolar</h1>
            <p>Please enter your credentials to log in.</p>
            <form id="login-form" data-endpoint="<?= site_url('login') ?>">
                <?= csrf_field() ?>
                <div class="form-fields">
                    <input type="text" placeholder="Email or username" name="login" autocomplete="username" required>
                    <div class="password-field">
                        <input type="password" placeholder="Password" name="password" required>
                        <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
                <button type="submit">Login</button>
                <p class="form-message" role="status" aria-live="polite"></p>
                <p>Don't have an account? <a href="<?= site_url('register') ?>">Register</a></p>
            </form>
            <p class="back-home"><a href="<?= site_url('/') ?>">Back to landing page</a></p>
        </div>
    </div>
</body>
</html>
