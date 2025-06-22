<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';

$cid = (int)($_GET['id'] ?? 0);
$params = ['cid' => $cid];
$sql = 'SELECT 1 FROM campaigns WHERE campaign_id = :cid';
if ($_SESSION['role'] !== 'admin') {
    $sql .= ' AND user_id = :u';
    $params['u'] = $_SESSION['user_id'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// If record exists & ownership ok:
if ($stmt->fetch()) {
    $pdo->prepare('DELETE FROM campaigns WHERE campaign_id = :cid')
        ->execute(['cid' => $cid]);
}

header('Location:' . BASE_URL . 'campaigns/list.php');
exit;
