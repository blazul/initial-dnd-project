<?php
// friends/send.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$me       = $_SESSION['user_id'];
$username = '';
$errors   = [];
$success  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $errors[] = 'Please enter a username.';
    } else {
        // Find the target user by username
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = :uname LIMIT 1');
        $stmt->execute(['uname' => $username]);
        $targetId = $stmt->fetchColumn();

        if (! $targetId) {
            $errors[] = 'No user found with that username.';
        } elseif ($targetId == $me) {
            $errors[] = 'You cannot friend yourself.';
        } else {
            // Check existing friendship or pending request
            $check = $pdo->prepare("
                SELECT COUNT(*) FROM friendships
                 WHERE (user_id = :me1 AND friend_id = :them1)
                    OR (user_id = :them2 AND friend_id = :me2)
            ");
            $check->execute([
                'me1'   => $me,
                'them1' => $targetId,
                'them2' => $targetId,
                'me2'   => $me,
            ]);

            if ($check->fetchColumn() > 0) {
                $errors[] = 'A friendship or request already exists with that user.';
            } else {
                // Insert the new pending request
                $ins = $pdo->prepare('
                    INSERT INTO friendships (user_id, friend_id)
                    VALUES (:me, :them)
                ');
                try {
                    $ins->execute(['me' => $me, 'them' => $targetId]);
                    $success = true;
                } catch (PDOException $e) {
                    $errors[] = 'Unable to send request. Please try again.';
                }
            }
        }
    }
}
?>

<section class="form-container">
  <h2>Send Friend Request</h2>

  <?php if ($success): ?>
    <p class="success">Friend request sent to “<?= htmlspecialchars($username) ?>”!</p>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post">
    <label for="username">Username</label>
    <input
      type="text"
      id="username"
      name="username"
      value="<?= htmlspecialchars($username) ?>"
      required
      autofocus
    >
    <button class="button">Send Request</button>
  </form>

  <p>
    <a href="<?= BASE_URL ?>friends/requests.php">View Incoming Requests</a>
    |
    <a href="<?= BASE_URL ?>friends/list.php">My Friends</a>
  </p>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
