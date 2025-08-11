<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/config.php';

$cid = (int)($_GET['id'] ?? 0);
$params = ['cid' => $cid];
$sql    = 'SELECT 1 FROM characters WHERE character_id = :cid';
if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND user_id = :u';
    $params['u']    = $_SESSION['user_id'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

if ($stmt->fetch()) {
    $pdo->prepare('DELETE FROM characters WHERE character_id = :cid')
        ->execute(['cid' => $cid]);
}

header('Location:' . BASE_URL . 'characters/list.php');
exit;
