<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

$cid = (int)($_GET['id'] ?? 0);
$me  = $_SESSION['user_id'];

$sql = "
  SELECT c.*, u.username AS owner
    FROM campaigns c
    JOIN users      u USING(user_id)
   WHERE c.campaign_id = :cid
";
$params = ['cid' => $cid];
if ($_SESSION['role'] !== 'admin') {
    $sql .= "
     AND (
       c.user_id = :me1
       OR EXISTS (
         SELECT 1 FROM campaign_shares s
          WHERE s.campaign_id = :cid2
            AND s.user_id   = :me2
       )
     )
    ";
    $params['me1']  = $me;
    $params['cid2'] = $cid;
    $params['me2']  = $me;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$camp = $stmt->fetch();
if (!$camp) {
    echo '<p>Not found or access denied.</p>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$inStmt = $pdo->prepare("
  SELECT c.character_id,
         c.name,
         c.character_class,
         c.race,
         c.level,
         u.username AS owner
    FROM characters c
    JOIN users u ON c.user_id = u.user_id
   WHERE c.campaign_id = :cid
   ORDER BY c.name
");
$inStmt->execute(['cid' => $cid]);
$inCampaign = $inStmt->fetchAll();

$ownStmt = $pdo->prepare("
  SELECT character_id,name,character_class,race,level
    FROM characters
   WHERE user_id = :u
   ORDER BY name
");
$ownStmt->execute(['u' => $me]);
$owned = $ownStmt->fetchAll();
?>

<section class="campaign-details">
  <h2><?= htmlspecialchars($camp['name']) ?></h2>
  <p><?= nl2br(htmlspecialchars($camp['description'])) ?></p>
  <p>
    <em>Created: <?= htmlspecialchars($camp['created_at']) ?></em><br>
    <em>Owner: <?= htmlspecialchars($camp['owner']) ?></em>
  </p>

  <?php if ($camp['user_id'] === $me || $_SESSION['role'] === 'admin'): ?>
    <p>
      <a href="<?= BASE_URL ?>campaigns/edit.php?id=<?= $cid ?>">Edit</a> |
      <a href="<?= BASE_URL ?>campaigns/delete.php?id=<?= $cid ?>"
         onclick="return confirm('Delete this campaign?');">Delete</a>
    </p>
    <hr>
  <?php endif; ?>

  <h3>Characters in This Campaign</h3>
  <form method="post" action="<?= BASE_URL ?>campaigns/update_characters.php">
    <input type="hidden" name="campaign_id" value="<?= $cid ?>">

    <ul>
      <?php foreach ($inCampaign as $ch): ?>
        <li>
          <label>
            <input
              type="checkbox"
              name="member[]"
              value="<?= $ch['character_id'] ?>"
              checked
            >
            <strong><?= htmlspecialchars($ch['name']) ?></strong>
            (<?= htmlspecialchars($ch['character_class']) ?>,
             <?= htmlspecialchars($ch['race']) ?>,
             lvl <?= (int)$ch['level'] ?>)
            — Owner: <?= htmlspecialchars($ch['owner']) ?>
          </label>
          &nbsp;[<a href="<?= BASE_URL ?>characters/view.php?id=<?= $ch['character_id'] ?>">View</a>]
        </li>
      <?php endforeach; ?>
    </ul>

    <h3>Your Other Characters</h3>
    <ul>
      <?php
      $inIds = array_column($inCampaign, 'character_id');
      foreach ($owned as $ch):
        if (in_array($ch['character_id'], $inIds, true)) continue;
      ?>
        <li>
          <label>
            <input
              type="checkbox"
              name="member[]"
              value="<?= $ch['character_id'] ?>"
            >
            <strong><?= htmlspecialchars($ch['name']) ?></strong>
            (<?= htmlspecialchars($ch['character_class']) ?>,
             <?= htmlspecialchars($ch['race']) ?>,
             lvl <?= (int)$ch['level'] ?>)
          </label>
          &nbsp;[<a href="<?= BASE_URL ?>characters/view.php?id=<?= $ch['character_id'] ?>">View</a>]
        </li>
      <?php endforeach; ?>
    </ul>

    <button class="button" onclick="return confirm('Save campaign character changes?');">
      Update Campaign
    </button>
  </form>

  <p><a href="<?= BASE_URL ?>campaigns/list.php">← Back to Campaigns</a></p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
