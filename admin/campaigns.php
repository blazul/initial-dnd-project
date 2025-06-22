<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$stmt = $pdo->query('
    SELECT cm.campaign_id, cm.name, u.username
      FROM campaigns cm
      JOIN users u ON cm.user_id = u.user_id
  ORDER BY cm.created_at DESC
');
$camps = $stmt->fetchAll();
?>

<section class="admin">
  <h2>All Campaigns</h2>
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Owner</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($camps as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['username']) ?></td>
        <td>
          <a href="<?= BASE_URL ?>campaigns/view.php?id=<?= $c['campaign_id'] ?>">View</a>
          | <a href="<?= BASE_URL ?>campaigns/edit.php?id=<?= $c['campaign_id'] ?>">Edit</a>
          | <a href="<?= BASE_URL ?>campaigns/delete.php?id=<?= $c['campaign_id'] ?>"
               onclick="return confirm('Delete this campaign?');">
              Delete
            </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
