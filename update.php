<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dashboard.php');
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$status = (string) ($_POST['status'] ?? '');
if (!$id || !in_array($status, ['Active', 'Paused', 'Cancelled'], true)) {
    flash('error', 'Invalid update request.');
    redirect('dashboard.php');
}
$stmt = $conn->prepare('UPDATE subscriptions SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $id);
$stmt->execute();
flash('success', $stmt->affected_rows ? 'Subscription status updated.' : 'No changes were required.');
redirect('dashboard.php');

