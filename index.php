<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
redirect(empty($_SESSION['user_id']) ? 'login.php' : 'dashboard.php');

