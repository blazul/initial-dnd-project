<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$cid    = (int)($_GET['id'] ?? 0);
$params = ['cid' => $cid];
$sql    = 'SELECT c.*, cam.name AS campaign_name FROM characters c LEFT JOIN campaigns cam USING(campaign_id) WHERE character_id = :cid';

if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND c.user_id = :u';
    $params['u']    = $_SESSION['user_id'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ch = $stmt->fetch();
if (!$ch) {
    echo '<p>Not found or access denied.</p>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Attributes
$attrStmt = $pdo->prepare('SELECT * FROM attributes WHERE character_id = :cid');
$attrStmt->execute(['cid' => $cid]);
$at = $attrStmt->fetch();

// Sessions
$sessStmt = $pdo->prepare('SELECT * FROM character_sessions WHERE character_id = :cid ORDER BY session_date DESC');
$sessStmt->execute(['cid' => $cid]);
$sessions = $sessStmt->fetchAll();
?>

<section class="character-details">
  <h2><?= htmlspecialchars($ch['name']) ?></h2>
  <?php if ($ch['campaign_name']): ?>
    <p><strong>Campaign:</strong> <?= htmlspecialchars($ch['campaign_name']) ?></p>
  <?php endif; ?>
  <p>
    <strong>Class:</strong> <?= htmlspecialchars($ch['character_class']) ?><br>
    <strong>Race:</strong> <?= htmlspecialchars($ch['race']) ?><br>
    <strong>Level:</strong> <?= (int)$ch['level'] ?><br>
    <strong>AC:</strong> <?= (int)$ch['armor_class'] ?>,
    <strong>HP:</strong> <?= (int)$ch['hit_points'] ?>,
    <strong>Speed:</strong> <?= (int)$ch['speed'] ?>,
    <strong>Init:</strong> <?= (int)$ch['initiative'] ?>
  </p>
  <p><?= nl2br(htmlspecialchars($ch['description'])) ?></p>

  <h3>Attributes</h3>
  <ul>
    <li>STR: <?= (int)$at['strength'] ?></li>
    <li>DEX: <?= (int)$at['dexterity'] ?></li>
    <li>CON: <?= (int)$at['constitution'] ?></li>
    <li>INT: <?= (int)$at['intelligence'] ?></li>
    <li>WIS: <?= (int)$at['wisdom'] ?></li>
    <li>CHA: <?= (int)$at['charisma'] ?></li>
  </ul>

  <h3>Session Notes</h3>
  <?php if (empty($sessions)): ?>
    <p>No sessions recorded.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($sessions as $s): ?>
        <li>
          <?= date('Y-m-d H:i', strtotime($s['session_date'])) ?>:
          <?= nl2br(htmlspecialchars($s['notes'])) ?>
          <div class="actions">
            <a href="<?= BASE_URL ?>sessions/edit.php?id=<?= $s['session_id'] ?>">Edit</a> |
            <a href="<?= BASE_URL ?>sessions/delete.php?id=<?= $s['session_id'] ?>"
               onclick="return confirm('Delete session note?');">Delete</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>sessions/add.php?id=<?= $cid ?>" class="button">+ Add Session Note</a>

  <hr>

  <div class="actions">
    <a href="<?= BASE_URL ?>characters/edit.php?id=<?= $cid ?>" class="button">Edit Character</a>
    <a href="<?= BASE_URL ?>characters/delete.php?id=<?= $cid ?>"
       class="button" onclick="return confirm('Delete this character?');">Delete Character</a>
    <a href="<?= BASE_URL ?>characters/list.php" class="button">Back to List</a>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
