<?php
// campaigns/list.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$uid = $_SESSION['user_id'];

// Fetch only this user’s campaigns
$stmt = $pdo->prepare(
    'SELECT
        c.campaign_id,
        c.name,
        c.description,
        c.created_at,
        u.username AS owner
     FROM campaigns c
     LEFT JOIN users u ON c.user_id = u.user_id
     WHERE c.user_id = :u
     ORDER BY c.created_at DESC'
);
$stmt->execute(['u' => $uid]);
$campaigns = $stmt->fetchAll();
?>

<section class="dashboard">
  <h2>My Campaigns</h2>
  <?php if (empty($campaigns)): ?>
    <p>No campaigns yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($campaigns as $cp): ?>
        <li>
          <strong><?= htmlspecialchars($cp['name']) ?></strong>
          <?php if ($cp['description']): ?>
            – <?= htmlspecialchars($cp['description']) ?>
          <?php endif; ?>
          <br>
          <small>Created: <?= date('Y-m-d', strtotime($cp['created_at'])) ?>
          | Owner: <?= htmlspecialchars($cp['owner']) ?></small>
          <br>
          <a href="<?= BASE_URL ?>campaigns/view.php?id=<?= $cp['campaign_id'] ?>">View</a>
          | <a href="<?= BASE_URL ?>campaigns/edit.php?id=<?= $cp['campaign_id'] ?>">Edit</a>
          | <a href="<?= BASE_URL ?>campaigns/delete.php?id=<?= $cp['campaign_id'] ?>"
               onclick="return confirm('Delete this campaign?');">Delete</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>campaigns/add.php" class="button">+ Add Campaign</a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
