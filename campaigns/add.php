<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$name = $description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO campaigns (user_id, name, description) VALUES (:u, :n, :d)');
        $stmt->execute([
            'u' => $_SESSION['user_id'],
            'n' => $name,
            'd' => $description
        ]);
        header('Location:' . BASE_URL . 'campaigns/list.php');
        exit;
    }
}
?>

<section class="form-container">
  <h2>Add Campaign</h2>
  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label>Description</label>
    <textarea name="description"><?= htmlspecialchars($description) ?></textarea>

    <button class="button">Create</button>
    <a href="<?= BASE_URL ?>campaigns/list.php" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
