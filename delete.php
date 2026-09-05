<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dashboard.php');
verify_csrf();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { flash('error', 'Invalid delete request.'); redirect('dashboard.php'); }
$stmt = $conn->prepare('DELETE FROM subscriptions WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
flash($stmt->affected_rows ? 'success' : 'error', $stmt->affected_rows ? 'Subscription deleted.' : 'Subscription not found.');
redirect('dashboard.php');

