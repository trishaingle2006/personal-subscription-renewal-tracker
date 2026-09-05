<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_login();

$search = trim((string) ($_GET['search'] ?? ''));
$category = (string) ($_GET['category'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$allowedCategories = ['Entertainment', 'Music', 'Fitness', 'Technology', 'Education', 'Food', 'Travel', 'Other'];
$allowedStatuses = ['Active', 'Paused', 'Cancelled'];

$sql = 'SELECT id, service_name, category, plan_name, billing_cycle, amount, renewal_date, status FROM subscriptions WHERE 1=1';
$types = '';
$params = [];
if ($search !== '') { $sql .= ' AND (service_name LIKE ? OR plan_name LIKE ?)'; $like = '%' . $search . '%'; $types .= 'ss'; $params[] = $like; $params[] = $like; }
if (in_array($category, $allowedCategories, true)) { $sql .= ' AND category = ?'; $types .= 's'; $params[] = $category; }
if (in_array($status, $allowedStatuses, true)) { $sql .= ' AND status = ?'; $types .= 's'; $params[] = $status; }
$sql .= ' ORDER BY renewal_date ASC, id DESC';
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$subscriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stats = $conn->query("SELECT COUNT(*) total, COALESCE(SUM(CASE WHEN status='Active' AND billing_cycle='Monthly' THEN amount WHEN status='Active' AND billing_cycle='Yearly' THEN amount/12 ELSE 0 END),0) monthly_cost, SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) active_count FROM subscriptions")->fetch_assoc();
$flash = take_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard | Renewly</title><link rel="stylesheet" href="assets/style.css"></head><body>
<header class="topbar"><a class="logo" href="dashboard.php"><span class="brand-mark small">R</span>Renewly</a><nav><a class="active" href="dashboard.php">Subscriptions</a><a href="add.php">Add new</a><a href="logout.php">Sign out</a></nav></header>
<main class="container"><section class="welcome"><div><p class="eyebrow">Subscription overview</p><h1>Hello, <?= e((string) $_SESSION['name']) ?></h1><p>Review upcoming renewals and keep recurring costs under control.</p></div><a class="btn primary" href="add.php">+ Add subscription</a></section>
<?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<section class="stats"><article><span>All subscriptions</span><strong><?= (int) $stats['total'] ?></strong></article><article><span>Active plans</span><strong><?= (int) $stats['active_count'] ?></strong></article><article class="accent"><span>Estimated monthly cost</span><strong>₹<?= number_format((float) $stats['monthly_cost'], 2) ?></strong></article></section>
<section class="panel"><div class="panel-heading"><div><h2>Your subscriptions</h2><p><?= count($subscriptions) ?> matching record<?= count($subscriptions) === 1 ? '' : 's' ?></p></div><form method="get" class="filters"><input name="search" value="<?= e($search) ?>" placeholder="Search service or plan"><select name="category"><option value="">All categories</option><?php foreach ($allowedCategories as $item): ?><option <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select><select name="status"><option value="">All statuses</option><?php foreach ($allowedStatuses as $item): ?><option <?= $status === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select><button class="btn secondary">Filter</button><a class="clear" href="dashboard.php">Clear</a></form></div>
<?php if (!$subscriptions): ?><div class="empty"><span>◎</span><h3>No subscriptions found</h3><p>Add a subscription or change the current filters.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Service</th><th>Category</th><th>Billing</th><th>Amount</th><th>Renews</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($subscriptions as $row): ?><tr><td><strong><?= e($row['service_name']) ?></strong><small><?= e($row['plan_name']) ?></small></td><td><?= e($row['category']) ?></td><td><?= e($row['billing_cycle']) ?></td><td>₹<?= number_format((float) $row['amount'], 2) ?></td><td><?= e(date('d M Y', strtotime($row['renewal_date']))) ?></td><td><span class="badge <?= strtolower(e($row['status'])) ?>"><?= e($row['status']) ?></span></td><td><div class="actions"><a href="edit.php?id=<?= (int) $row['id'] ?>">Edit</a><form method="post" action="delete.php" onsubmit="return confirm('Delete this subscription?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></main></body></html>

