<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/config.php';

$uid    = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (in_array($action, ['disable', 'enable'], true)) {
    $flag = $action === 'enable' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE users SET is_active = :flag WHERE user_id = :uid');
    $stmt->execute(['flag'=>$flag,'uid'=>$uid]);
}

header('Location:' . BASE_URL . 'admin_users.php');
exit;
