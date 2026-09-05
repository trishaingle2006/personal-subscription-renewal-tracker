<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

$userCount = (int) $conn->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = $conn->prepare('SELECT id, name, username, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['name'] = $user['name'];
        redirect('dashboard.php');
    }
    $error = 'Incorrect username or password.';
}
$flash = take_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in | Renewly</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="auth-page"><main class="auth-shell"><section class="auth-brand"><span class="brand-mark">R</span><p class="eyebrow">Subscription control, simplified</p><h1>Know what renews next.</h1><p>Keep every plan, price, and renewal date together in one calm workspace.</p><div class="auth-preview"><span>Next renewal</span><strong>Cloud storage</strong><b>12 Sep · ₹199</b></div></section><section class="auth-card"><h2>Welcome back</h2><p class="muted">Sign in to manage your subscriptions.</p><?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if ($userCount === 0): ?><div class="alert info">No administrator exists yet. <a href="setup.php">Complete first-time setup</a>.</div><?php endif; ?><form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label>Username<input name="username" required maxlength="30" autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn primary" type="submit">Sign in</button></form></section></main></body></html>

