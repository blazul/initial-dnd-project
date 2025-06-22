<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$uid = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT user_id, username, email, role, is_active FROM users WHERE user_id = :uid');
$stmt->execute(['uid'=>$uid]);
$user = $stmt->fetch();
if (!$user) {
    header('Location:' . BASE_URL . 'admin_users.php');
    exit;
}

$errors = [];
$username = $user['username'];
$email = $user['email'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? $role;
    $newPass  = $_POST['password'] ?? '';

    if ($username === '' || $email === '') {
        $errors[] = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    } else {
        $stmt = $pdo->prepare('
            UPDATE users
               SET username = :u, email = :e, role = :r
             WHERE user_id = :uid
        ');
        $stmt->execute(['u'=>$username,'e'=>$email,'r'=>$role,'uid'=>$uid]);

        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = :p WHERE user_id = :uid')
                ->execute(['p'=>$hash,'uid'=>$uid]);
        }

        header('Location:' . BASE_URL . 'admin_users.php');
        exit;
    }
}
?>

<section class="form-container">
  <h2>Edit User</h2>
  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>
  <form method="post">
    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

    <label>Role</label>
    <select name="role">
      <option value="user" <?= $role==='user' ? 'selected' : '' ?>>User</option>
      <option value="admin" <?= $role==='admin' ? 'selected' : '' ?>>Admin</option>
    </select>

    <label>New Password (leave blank to keep)</label>
    <input type="password" name="password">

    <button class="button">Save</button>
    <a href="<?= BASE_URL ?>admin_users.php" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
