<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$_SESSION = [];
session_unset();
session_destroy();

header('Location:' . BASE_URL . 'public/index.php');
exit;
