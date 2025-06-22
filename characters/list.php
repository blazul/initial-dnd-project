<?php
// characters/list.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$uid = $_SESSION['user_id'];

// Fetch only this user’s characters
$stmt = $pdo->prepare(
    'SELECT
        c.character_id,
        c.name,
        c.race,
        c.character_class,
        c.level,
        cam.name AS campaign_name
     FROM characters c
     LEFT JOIN campaigns cam ON c.campaign_id = cam.campaign_id
     WHERE c.user_id = :u
     ORDER BY c.name ASC'
);
$stmt->execute(['u' => $uid]);
$characters = $stmt->fetchAll();
?>

<section class="dashboard">
  <h2>My Characters</h2>
  <?php if (empty($characters)): ?>
    <p>No characters yet.</p>
  <?php else: ?>
    <div class="character-list">
      <?php foreach ($characters as $ch): ?>
        <div class="character-card">
          <h3><?= htmlspecialchars($ch['name']) ?></h3>
          <p>
            <strong>Class:</strong> <?= htmlspecialchars($ch['character_class']) ?><br>
            <strong>Race:</strong> <?= htmlspecialchars($ch['race']) ?><br>
            <strong>Level:</strong> <?= (int)$ch['level'] ?><br>
            <?php if ($ch['campaign_name']): ?>
              <strong>Campaign:</strong> <?= htmlspecialchars($ch['campaign_name']) ?>
            <?php endif; ?>
          </p>
          <div class="actions">
            <a href="<?= BASE_URL ?>characters/view.php?id=<?= $ch['character_id'] ?>">View</a>
            | <a href="<?= BASE_URL ?>characters/edit.php?id=<?= $ch['character_id'] ?>">Edit</a>
            | <a href="<?= BASE_URL ?>characters/delete.php?id=<?= $ch['character_id'] ?>"
                 onclick="return confirm('Delete this character?');">Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>characters/add.php" class="button">+ Add Character</a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
