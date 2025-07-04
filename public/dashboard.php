<?php
// public/dashboard.php

require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$uid  = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>

<section class="dashboard">

  <!-- MY CAMPAIGNS -->
  <h2>My Campaigns</h2>
  <?php
    $stmt = $pdo->prepare('
      SELECT campaign_id, name
        FROM campaigns
       WHERE user_id = :u
    ORDER BY created_at DESC
    ');
    $stmt->execute(['u'=>$uid]);
    $myCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <?php if (empty($myCampaigns)): ?>
    <p>No campaigns yet.</p>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
      <?php foreach ($myCampaigns as $cp): ?>
        <div class="col">
          <div class="card h-100">
            <div class="card-body d-flex justify-content-between align-items-start">
              <a href="<?= BASE_URL ?>campaigns/view.php?id=<?= $cp['campaign_id'] ?>"
                 class="card-title h5 mb-0">
                <?= htmlspecialchars($cp['name'], ENT_QUOTES) ?>
              </a>
              <div>
                <a href="<?= BASE_URL ?>campaigns/edit.php?id=<?= $cp['campaign_id'] ?>"
                   class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                <a href="<?= BASE_URL ?>campaigns/delete.php?id=<?= $cp['campaign_id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this campaign?');">
                  Delete
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>campaigns/add.php" class="btn btn-primary mb-5">+ Add Campaign</a>

  <hr>

  <!-- MY CHARACTERS -->
  <h2>My Characters</h2>
  <?php
    $stmt = $pdo->prepare('
      SELECT
        c.character_id,
        c.name,
        c.race,
        c.character_class,
        c.level,
        cam.name AS campaign_name
      FROM characters AS c
      LEFT JOIN campaigns AS cam
        ON c.campaign_id = cam.campaign_id
     WHERE c.user_id = :u
  ORDER BY c.name ASC
    ');
    $stmt->execute(['u'=>$uid]);
    $myChars = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <?php if (empty($myChars)): ?>
    <p>No characters yet.</p>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
      <?php foreach ($myChars as $ch): ?>
        <div class="col">
          <div class="card h-100">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($ch['name'], ENT_QUOTES) ?></h5>
              <p class="card-text mb-2">
                <strong>Class:</strong> <?= htmlspecialchars($ch['character_class'], ENT_QUOTES) ?><br>
                <strong>Race:</strong> <?= htmlspecialchars($ch['race'], ENT_QUOTES) ?><br>
                <strong>Level:</strong> <?= (int)$ch['level'] ?><br>
                <?php if ($ch['campaign_name']): ?>
                  <strong>Campaign:</strong> <?= htmlspecialchars($ch['campaign_name'], ENT_QUOTES) ?>
                <?php endif; ?>
              </p>
              <div class="mt-auto">
                <a href="<?= BASE_URL ?>characters/view.php?id=<?= $ch['character_id'] ?>"
                   class="btn btn-sm btn-outline-primary me-1">View</a>
                <a href="<?= BASE_URL ?>characters/edit.php?id=<?= $ch['character_id'] ?>"
                   class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                <a href="<?= BASE_URL ?>characters/delete.php?id=<?= $ch['character_id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this character?');">
                  Delete
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>characters/add.php" class="btn btn-primary">+ Add Character</a>

  <?php if ($role === 'admin'): ?>
    <hr>

    <!-- ADMIN PANEL -->
    <section class="admin-panel">
      <h2>Admin Panel</h2>
      <p>
        <a href="<?= BASE_URL ?>admin/users.php" class="btn btn-outline-dark mb-3">
          Manage Users
        </a>
      </p>

      <!-- ALL CAMPAIGNS -->
      <h3>All Campaigns</h3>
      <?php
        $stmt = $pdo->query('
          SELECT campaign_id, name
            FROM campaigns
        ORDER BY created_at DESC
        ');
        $allCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <?php if (empty($allCampaigns)): ?>
        <p>No campaigns in the system.</p>
      <?php else: ?>
        <ul class="list-group mb-4">
          <?php foreach ($allCampaigns as $cp): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($cp['name'], ENT_QUOTES) ?>
              <div>
                <a href="<?= BASE_URL ?>campaigns/edit.php?id=<?= $cp['campaign_id'] ?>"
                   class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                <a href="<?= BASE_URL ?>campaigns/delete.php?id=<?= $cp['campaign_id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this campaign?');">
                  Delete
                </a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <!-- ALL CHARACTERS -->
      <h3>All Characters</h3>
      <?php
        $stmt = $pdo->query('
          SELECT
            c.character_id,
            c.name,
            c.race,
            c.character_class,
            c.level,
            u.username,
            cam.name AS campaign_name
          FROM characters AS c
          LEFT JOIN users      AS u   ON c.user_id     = u.user_id
          LEFT JOIN campaigns  AS cam ON c.campaign_id = cam.campaign_id
         ORDER BY c.name ASC
        ');
        $allChars = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <?php if (empty($allChars)): ?>
        <p>No characters in the system.</p>
      <?php else: ?>
        <ul class="list-group">
          <?php foreach ($allChars as $ch): ?>
            <li class="list-group-item">
              <strong><?= htmlspecialchars($ch['name'], ENT_QUOTES) ?></strong>
              (<?= htmlspecialchars($ch['username'], ENT_QUOTES) ?>’s,
               <?= htmlspecialchars($ch['character_class'], ENT_QUOTES) ?>,
               lvl <?= (int)$ch['level'] ?>,
               <?= $ch['campaign_name'] ? htmlspecialchars($ch['campaign_name'], ENT_QUOTES) : 'No campaign' ?>)
              <div class="float-end">
                <a href="<?= BASE_URL ?>characters/edit.php?id=<?= $ch['character_id'] ?>"
                   class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                <a href="<?= BASE_URL ?>characters/delete.php?id=<?= $ch['character_id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this character?');">
                  Delete
                </a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    </section>
  <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
