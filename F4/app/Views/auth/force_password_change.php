<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change temporary password | Sun Son Solar</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/portal.css') ?>">
</head>
<body class="portal-page">
    <main class="auth-container" style="min-height:100vh;display:grid;place-items:center;padding:24px">
        <section class="portal-panel" style="width:min(100%,480px);padding:28px">
            <a class="brand" href="<?= site_url('/') ?>"><img src="<?= base_url('assets/logo-sss.png') ?>" alt=""><span>Sun Son <b>Solar</b></span></a>
            <p class="eyebrow" style="margin-top:28px">First sign-in</p>
            <h1 style="font-size:28px">Set your own password, <?= esc($name) ?>.</h1>
            <p style="margin:12px 0 22px;color:var(--muted);font-size:13px;line-height:1.6">Your temporary password needs to be replaced before you continue to your team workspace.</p>
            <?php if (!empty($message)): ?><div class="flash-message"><?= esc($message) ?></div><?php endif ?>
            <?php if (!empty($error)): ?><div class="flash-message flash-error"><?= esc($error) ?></div><?php endif ?>
            <form class="portal-form" method="post" action="<?= site_url('portal/password') ?>">
                <?= csrf_field() ?>
                <label class="form-wide">Temporary password<input type="password" name="current_password" autocomplete="current-password" required></label>
                <label class="form-wide">New password<input type="password" name="new_password" autocomplete="new-password" minlength="12" required></label>
                <button type="submit">Change password</button>
            </form>
            <a class="text-link" style="margin-top:20px" href="<?= site_url('logout') ?>">Log out</a>
        </section>
    </main>
</body>
</html>