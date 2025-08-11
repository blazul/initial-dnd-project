<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

$sid = (int)($_GET['id'] ?? 0);
// Fetch session & verify ownership
$sql = '
    SELECT cs.character_id, cs.session_date, cs.notes
      FROM character_sessions cs
      JOIN characters c ON cs.character_id = c.character_id
     WHERE cs.session_id = :sid
';
$params = ['sid' => $sid];
if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND c.user_id = :uid';
    $params['uid']  = $_SESSION['user_id'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$session = $stmt->fetch();
if (! $session) {
    header('Location:' . BASE_URL . 'characters/list.php');
    exit;
}

$cid = $session['character_id'];
$session_date = date('Y-m-d\TH:i', strtotime($session['session_date']));
$notes = $session['notes'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_date = trim($_POST['session_date'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    if ($session_date === '') {
        $errors[] = 'Date & time is required.';
    }

    if (empty($errors)) {
        $dt = date('Y-m-d H:i:s', strtotime($session_date));
        $upd = $pdo->prepare('
            UPDATE character_sessions
               SET session_date = :dt, notes = :notes
             WHERE session_id = :sid
        ');
        $upd->execute([
            'dt'    => $dt,
            'notes' => $notes,
            'sid'   => $sid
        ]);
        header('Location:' . BASE_URL . "characters/view.php?id={$cid}");
        exit;
    }
}
?>

<section class="form-container">
  <h2>Edit Session Note</h2>
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

    <button class="button">Save Changes</button>
    <a href="<?= BASE_URL ?>characters/view.php?id=<?= $cid ?>" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
