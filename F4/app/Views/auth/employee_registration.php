<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee accounts | Sun Son Solar</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/portal.css') ?>">
</head>
<body class="portal-page">
    <main class="auth-container" style="min-height:100vh;display:grid;place-items:center;padding:24px">
        <section class="portal-panel" style="width:min(100%,480px);padding:28px;text-align:center">
            <a class="brand" href="<?= site_url('/') ?>"><img src="<?= base_url('assets/logo-sss.png') ?>" alt=""><span>Sun Son <b>Solar</b></span></a>
            <h1 style="margin-top:28px;font-size:28px">Employee accounts are created by HR or an administrator.</h1>
            <p style="margin:14px 0 22px;color:var(--muted);font-size:13px;line-height:1.6">Ask your HR team or administrator for your username and temporary password. You’ll be asked to change the password at your first sign-in.</p>
            <a class="button button-dark" href="<?= site_url('login') ?>">Go to sign in</a>
        </section>
    </main>
</body>
</html>
