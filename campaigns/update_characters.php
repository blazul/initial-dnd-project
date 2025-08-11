<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_login();
    require_once __DIR__ . '/../includes/config.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('This page only accepts POST');
    }

    $me      = $_SESSION['user_id'] ?? null;
    $cid     = isset($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : 0;
    $members = $_POST['member'] ?? [];

    if (!$me) {
        throw new Exception('Not logged in');
    }
    if ($cid < 1) {
        throw new Exception('Invalid campaign ID');
    }
    if (!is_array($members)) {
        $members = [];
    }
    $members = array_map('intval', $members);

    $stmt = $pdo->prepare('SELECT user_id FROM campaigns WHERE campaign_id = :cid');
    $stmt->execute(['cid' => $cid]);
    $owner = $stmt->fetchColumn();
    if ($owner === false) {
        throw new Exception("Campaign #{$cid} not found");
    }
    $canAccess = ($_SESSION['role'] === 'admin' || $owner == $me);
    if (!$canAccess) {
        $chk = $pdo->prepare('SELECT 1 FROM campaign_shares WHERE campaign_id = :cid AND user_id = :u');
        $chk->execute(['cid' => $cid, 'u' => $me]);
        $canAccess = (bool)$chk->fetchColumn();
    }
    if (!$canAccess) {
        throw new Exception('Access denied to this campaign');
    }

    $stmt = $pdo->prepare('SELECT character_id FROM characters WHERE user_id = :u');
    $stmt->execute(['u' => $me]);
    $yourChars = array_column($stmt->fetchAll(), 'character_id');

    $addStmt    = $pdo->prepare('UPDATE characters SET campaign_id = :cid WHERE character_id = :chid');
    $removeStmt = $pdo->prepare('UPDATE characters SET campaign_id = NULL WHERE character_id = :chid');

    foreach ($yourChars as $chid) {
        if (in_array($chid, $members, true)) {
            $addStmt->execute(['cid' => $cid, 'chid' => $chid]);
        } else {
            $removeStmt->execute(['chid' => $chid]);
        }
    }

    header('Location: view.php?id=' . $cid);
    exit;
} catch (Throwable $e) {
    echo "<h1>Error updating campaign characters</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}
