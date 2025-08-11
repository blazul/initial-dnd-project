<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/config.php';

$sid = (int)($_GET['id'] ?? 0);
// Verify session belongs to user (admins bypass)
$sql = '
    SELECT cs.character_id
      FROM character_sessions cs
      JOIN characters c USING(character_id)
     WHERE cs.session_id = :sid
';
$params = ['sid' => $sid];
if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND c.user_id = :uid';
    $params['uid']  = $_SESSION['user_id'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$session = $stmt->fetch();
if (! $session) {
    header('Location:' . BASE_URL . 'characters/list.php');
    exit;
}

$cid = $session['character_id'];
$pdo->prepare('DELETE FROM character_sessions WHERE session_id = :sid')
    ->execute(['sid' => $sid]);

header('Location:' . BASE_URL . "characters/view.php?id={$cid}");
exit;
