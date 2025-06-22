<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
http_response_code(404);
?>

<section class="form-container">
  <h2>404 — Page Not Found</h2>
  <p>Sorry, the page you were looking for doesn’t exist.</p>
  <a href="<?= BASE_URL ?>public/index.php" class="button">Return Home</a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
