<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$stmt = $pdo->query('
    SELECT c.character_id, c.name, c.character_class, u.username
      FROM characters c
      JOIN users u ON c.user_id = u.user_id
  ORDER BY c.name
');
$chars = $stmt->fetchAll();
?>

<section class="admin">
  <h2>All Characters</h2>
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Class</th>
        <th>Owner</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($chars as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['character_class']) ?></td>
        <td><?= htmlspecialchars($c['username']) ?></td>
        <td>
          <a href="<?= BASE_URL ?>characters/view.php?id=<?= $c['character_id'] ?>">View</a>
          | <a href="<?= BASE_URL ?>characters/edit.php?id=<?= $c['character_id'] ?>">Edit</a>
          | <a href="<?= BASE_URL ?>characters/delete.php?id=<?= $c['character_id'] ?>"
               onclick="return confirm('Delete this character?');">
              Delete
            </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>