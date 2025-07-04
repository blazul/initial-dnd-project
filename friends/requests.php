<?php
// friends/requests.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$me = $_SESSION['user_id'];

// Handle accept/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fid    = (int)($_POST['friendship_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($fid && in_array($action, ['accepted','rejected'], true)) {
        $upd = $pdo->prepare("
          UPDATE friendships
             SET status = :st, responded_at = NOW()
           WHERE friendship_id = :fid
             AND friend_id = :me
        ");
        $upd->execute(['st'=>$action,'fid'=>$fid,'me'=>$me]);
    }
}

// Load incoming pending requests
$stmt = $pdo->prepare("
  SELECT f.friendship_id, u.user_id, u.username, f.requested_at
    FROM friendships f
    JOIN users       u ON f.user_id   = u.user_id
   WHERE f.friend_id = :me
     AND f.status    = 'pending'
   ORDER BY f.requested_at DESC
");
$stmt->execute(['me'=>$me]);
$requests = $stmt->fetchAll();
?>

<section class="dashboard">
  <h2>Friend Requests</h2>
  <?php if (empty($requests)): ?>
    <p>No pending requests.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($requests as $r): ?>
        <li>
          <?= htmlspecialchars($r['username']) ?>
          (requested at <?= htmlspecialchars($r['requested_at']) ?>)
          <form method="post" style="display:inline">
            <input type="hidden" name="friendship_id" value="<?= $r['friendship_id'] ?>">
            <button name="action" value="accepted">Accept</button>
            <button name="action" value="rejected">Reject</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
