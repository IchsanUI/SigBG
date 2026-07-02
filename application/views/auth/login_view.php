<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SigBG Digital Signage</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css'); ?>">
</head>
<body class="login-page">
    <div class="login-box">
        <h1 class="h4 mb-1" style="font-weight: 600;">SigBG</h1>
        <p class="text-secondary small-text mb-4">Digital Signage Admin</p>

        <?php if (isset($locked) && $locked): ?>
            <div class="alert-login">
                <strong>Locked.</strong>
                Too many failed attempts. Try again in
                <?= ceil($locked_secs / 60) ?> minute(s).
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="alert-login">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (validation_errors()): ?>
            <div class="alert-login"><?= validation_errors(); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('auth'); ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-control"
                    value="<?= set_value('username'); ?>"
                    autocomplete="username"
                    required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    autocomplete="current-password"
                    required>
            </div>

            <button type="submit" class="btn btn-accent w-100 mt-2">
                Sign In
            </button>
        </form>

        <p class="small-text mt-4 mb-0 text-center text-secondary">
            Default: <code>admin</code> / <code>admin123</code>
        </p>
    </div>
</body>
</html>
