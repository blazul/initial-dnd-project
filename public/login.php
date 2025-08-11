<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (! empty($_SESSION['user_id'])) {
    header('Location:' . BASE_URL . 'public/dashboard.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$errors = [];
$login  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $errors[] = 'Both fields are required.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT user_id, username, password, role, is_active
               FROM users
              WHERE username = :l1 OR email = :l2
              LIMIT 1'
        );
        $stmt->execute(['l1' => $login, 'l2' => $login]);
        $user = $stmt->fetch();

        if (! $user) {
            $errors[] = 'No account matches that login.';
        } elseif (! $user['is_active']) {
            $errors[] = 'Account is deactivated.';
        } elseif (! password_verify($password, $user['password'])) {
            $errors[] = 'Incorrect password.';
        } else {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location:' . BASE_URL . 'public/dashboard.php');
            exit;
        }
    }
}
?>

<section class="form-container">
  <h2>Log In</h2>
  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post">
    <label>Username or Email</label>
    <input type="text" name="login" value="<?= htmlspecialchars($login) ?>" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button class="button">Log In</button>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
