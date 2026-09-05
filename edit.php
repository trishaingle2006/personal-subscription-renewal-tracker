<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) redirect('dashboard.php');
$stmt = $conn->prepare('SELECT id, service_name, status FROM subscriptions WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();
if (!$subscription) { flash('error', 'Subscription not found.'); redirect('dashboard.php'); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Update status | Renewly</title><link rel="stylesheet" href="assets/style.css"></head><body><header class="topbar"><a class="logo" href="dashboard.php"><span class="brand-mark small">R</span>Renewly</a><nav><a href="dashboard.php">Subscriptions</a><a href="add.php">Add new</a><a href="logout.php">Sign out</a></nav></header><main class="container narrow"><a class="back" href="dashboard.php">← Back to subscriptions</a><section class="panel form-panel"><p class="eyebrow">Update record</p><h1><?= e($subscription['service_name']) ?></h1><p class="muted">Change the current subscription status.</p><form action="update.php" method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $subscription['id'] ?>"><label>Status<select name="status" required><?php foreach (['Active', 'Paused', 'Cancelled'] as $item): ?><option <?= $subscription['status'] === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></label><div class="form-actions"><a class="btn secondary" href="dashboard.php">Cancel</a><button class="btn primary" type="submit">Update status</button></div></form></section></main></body></html>

