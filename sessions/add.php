<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

$cid = (int)($_GET['id'] ?? 0);
// Verify character ownership (admins bypass this)
$params = ['cid' => $cid];
$sql = 'SELECT 1 FROM characters WHERE character_id = :cid';
if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND user_id = :uid';
    $params['uid']  = $_SESSION['user_id'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
if (! $stmt->fetch()) {
    header('Location:' . BASE_URL . 'characters/list.php');
    exit;
}

$errors = [];
$session_date = '';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_date = trim($_POST['session_date'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    if ($session_date === '') {
        $errors[] = 'Date & time is required.';
    }

    if (empty($errors)) {
        $dt = date('Y-m-d H:i:s', strtotime($session_date));
        $ins = $pdo->prepare('
            INSERT INTO character_sessions (character_id, session_date, notes)
            VALUES (:cid, :dt, :notes)
        ');
        $ins->execute([
            'cid'   => $cid,
            'dt'    => $dt,
            'notes' => $notes
        ]);
        header('Location:' . BASE_URL . "characters/view.php?id={$cid}");
        exit;
    }
}
?>

<section class="form-container">
  <h2>Add Session Note</h2>
  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post">
    <label>Date & Time</label>
    <input type="datetime-local"
           name="session_date"
           value="<?= htmlspecialchars($session_date) ?>"
           required>

    <label>Notes</label>
    <textarea name="notes"><?= htmlspecialchars($notes) ?></textarea>

    <button class="button">Save</button>
    <a href="<?= BASE_URL ?>characters/view.php?id=<?= $cid ?>" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
