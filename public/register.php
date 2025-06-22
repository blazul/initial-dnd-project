<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (! empty($_SESSION['user_id'])) {
    header('Location:' . BASE_URL . 'public/dashboard.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $confirm_pass === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    } elseif ($password !== $confirm_pass) {
        $errors[] = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u OR email = :e');
        $stmt->execute(['u'=>$username,'e'=>$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username or email already taken.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username,email,password) VALUES (:u,:e,:p)');
        $stmt->execute(['u'=>$username,'e'=>$email,'p'=>$hash]);

        $_SESSION['user_id']  = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['role']     = 'user';

        header('Location:' . BASE_URL . 'public/dashboard.php');
        exit;
    }
}
?>

<section class="form-container">
  <h2>Register</h2>
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

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Confirm Password</label>
    <input type="password" name="confirm_password" required>

    <button class="button">Sign Up</button>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
