<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$count = (int) $conn->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'];
if ($count > 0) {
    redirect('login.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) $errors[] = 'Name must contain 2 to 80 characters.';
    if (!preg_match('/^[A-Za-z0-9_.-]{4,30}$/', $username)) $errors[] = 'Username must contain 4 to 30 letters, numbers, dots, dashes, or underscores.';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, username, password_hash) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $username, $hash);
        $stmt->execute();
        flash('success', 'Administrator account created. You can now sign in.');
        redirect('login.php');
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Set up Renewly</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="auth-page"><main class="auth-shell"><section class="auth-brand"><span class="brand-mark">R</span><p class="eyebrow">First-time setup</p><h1>Create the administrator account.</h1><p>Your password is stored as a secure hash. This setup page locks automatically after the first account is created.</p></section><section class="auth-card"><h2>Set up Renewly</h2><p class="muted">Create the account used to manage subscriptions.</p><?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Full name<input name="name" minlength="2" maxlength="80" required autocomplete="name"></label><label>Username<input name="username" minlength="4" maxlength="30" pattern="[A-Za-z0-9_.-]+" required autocomplete="username"></label><label>Password<input type="password" name="password" minlength="8" required autocomplete="new-password"></label><button class="btn primary" type="submit">Create account</button></form></section></main></body></html>

