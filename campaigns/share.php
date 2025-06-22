<?php
// campaigns/share.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';

$me = $_SESSION['user_id'];
$cid = (int)($_POST['campaign_id'] ?? 0);

// Verify ownership
$stmt = $pdo->prepare("SELECT user_id FROM campaigns WHERE campaign_id = :cid");
$stmt->execute(['cid'=>$cid]);
$owner = $stmt->fetchColumn();
if ($owner != $me && $_SESSION['role'] !== 'admin') {
    exit('Access denied.');
}

// Process shares
$pdo->prepare("DELETE FROM campaign_shares WHERE campaign_id = :cid")
    ->execute(['cid'=>$cid]);

$friends = $_POST['friends'] ?? [];
if (is_array($friends)) {
    $ins = $pdo->prepare("
      INSERT INTO campaign_shares (campaign_id, user_id)
      VALUES (:cid, :uid)
    ");
    foreach ($friends as $uid) {
        $ins->execute(['cid'=>$cid,'uid'=>(int)$uid]);
    }
}

// Redirect back
header('Location: ' . BASE_URL . "campaigns/view.php?id={$cid}");
exit;
