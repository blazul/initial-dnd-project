<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$stmt = $pdo->query('
    SELECT user_id, username, email, role, is_active, created_at
      FROM users
  ORDER BY created_at DESC
');
$users = $stmt->fetchAll();
?>

<section class="admin">
  <h2>Manage Users</h2>
  <table>
    <thead>
      <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= $u['is_active'] ? 'Active' : 'Disabled' ?></td>
        <td><?= htmlspecialchars($u['created_at']) ?></td>
        <td>
          <a href="<?= BASE_URL ?>admin/edit_user.php?id=<?= $u['user_id'] ?>">Edit</a>
          <?php if ($u['is_active']): ?>
            | <a href="<?= BASE_URL ?>admin/toggle_user.php?id=<?= $u['user_id'] ?>&action=disable"
                 onclick="return confirm('Disable this account?');">
                Disable
              </a>
          <?php else: ?>
            | <a href="<?= BASE_URL ?>admin/toggle_user.php?id=<?= $u['user_id'] ?>&action=enable">
                Enable
              </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
