<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

// Redirect guests to login, authenticated users to dashboard
if (! empty($_SESSION['user_id'])) {
    header('Location:' . BASE_URL . 'public/dashboard.php');
} else {
    header('Location:' . BASE_URL . 'public/login.php');
}
exit;
