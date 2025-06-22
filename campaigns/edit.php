<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$cid    = (int)($_GET['id'] ?? 0);
$errors = [];

$params = ['cid' => $cid];
$sql    = 'SELECT * FROM campaigns WHERE campaign_id = :cid';
if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND user_id = :u';
    $params['u']    = $_SESSION['user_id'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$camp = $stmt->fetch();

if (! $camp) {
    echo '<p>Not found or access denied.</p>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$name        = $camp['name'];
$description = $camp['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (empty($errors)) {
        $upd = $pdo->prepare('
            UPDATE campaigns
               SET name = :n,
                   description = :d,
                   updated_at = CURRENT_TIMESTAMP
             WHERE campaign_id = :cid
        ');
        $upd->execute([
            'n'   => $name,
            'd'   => $description,
            'cid' => $cid
        ]);
        header('Location:' . BASE_URL . 'campaigns/list.php');
        exit;
    }
}
?>

<section class="form-container">
  <h2>Edit Campaign</h2>
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

    <button class="button">Save</button>
    <a href="<?= BASE_URL ?>campaigns/list.php" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
