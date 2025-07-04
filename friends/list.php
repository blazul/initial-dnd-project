<?php
// friends/list.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$me = $_SESSION['user_id'];

// Fetch accepted friends in either direction
$sql = "
    SELECT u.user_id, u.username
      FROM friendships f
      JOIN users u 
        ON (f.user_id = :me1 AND f.friend_id = u.user_id)
        OR (f.friend_id = :me2 AND f.user_id = u.user_id)
     WHERE f.status = 'accepted'
  ORDER BY u.username
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'me1' => $me,
    'me2' => $me,
]);
$friends = $stmt->fetchAll();
?>

<section class="dashboard">
  <h2>My Friends</h2>
  <?php if (empty($friends)): ?>
    <p>You have no friends yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($friends as $f): ?>
        <li>
          <?= htmlspecialchars($f['username']) ?>
          <form method="post" action="<?= BASE_URL ?>friends/remove.php" style="display:inline">
            <input type="hidden" name="friend_id" value="<?= $f['user_id'] ?>">
            <button type="submit">Remove</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
