<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('add.php');
verify_csrf();

$service = trim((string) ($_POST['service_name'] ?? ''));
$category = (string) ($_POST['category'] ?? '');
$plan = trim((string) ($_POST['plan_name'] ?? ''));
$cycle = (string) ($_POST['billing_cycle'] ?? '');
$amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
$renewal = (string) ($_POST['renewal_date'] ?? '');
$status = (string) ($_POST['status'] ?? '');
$categories = ['Entertainment', 'Music', 'Fitness', 'Technology', 'Education', 'Food', 'Travel', 'Other'];
$statuses = ['Active', 'Paused', 'Cancelled'];
$cycles = ['Monthly', 'Yearly'];
$date = DateTime::createFromFormat('Y-m-d', $renewal);

if (mb_strlen($service) < 2 || mb_strlen($service) > 100 || mb_strlen($plan) < 2 || mb_strlen($plan) > 100 || !in_array($category, $categories, true) || !in_array($cycle, $cycles, true) || !in_array($status, $statuses, true) || $amount === false || $amount < 0 || $amount > 999999.99 || !$date || $date->format('Y-m-d') !== $renewal) {
    flash('error', 'Please enter valid values in every field.');
    redirect('add.php');
}

$stmt = $conn->prepare('INSERT INTO subscriptions (service_name, category, plan_name, billing_cycle, amount, renewal_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssdss', $service, $category, $plan, $cycle, $amount, $renewal, $status);
$stmt->execute();
flash('success', 'Subscription added successfully.');
redirect('dashboard.php');

