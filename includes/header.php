<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>D&amp;D Character Manager</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
  <header>
    <h1><a href="<?= BASE_URL ?>public/index.php">D&amp;D Character Manager</a></h1>
    <nav>
      <ul>
        <?php if (empty($_SESSION['user_id'])): ?>
          <li><a href="<?= BASE_URL ?>public/index.php">Home</a></li>
          <li><a href="<?= BASE_URL ?>public/login.php">Log In</a></li>
          <li><a href="<?= BASE_URL ?>public/register.php">Register</a></li>
        <?php else: ?>
          <li><a href="<?= BASE_URL ?>public/dashboard.php">Dashboard</a></li>
          <li><a href="<?= BASE_URL ?>campaigns/list.php">Campaigns</a></li>
          <li><a href="<?= BASE_URL ?>characters/list.php">Characters</a></li>

          <!-- Friends Menu -->
          <li>
            Friends
            <ul class="dropdown">
              <li><a href="<?= BASE_URL ?>friends/send.php">Send Request</a></li>
              <li><a href="<?= BASE_URL ?>friends/requests.php">Incoming Requests</a></li>
              <li><a href="<?= BASE_URL ?>friends/list.php">My Friends</a></li>
            </ul>
          </li>

          <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <li><a href="<?= BASE_URL ?>admin/users.php">Manage Users</a></li>
            <li><a href="<?= BASE_URL ?>admin/characters.php">All Characters</a></li>
            <li><a href="<?= BASE_URL ?>admin/campaigns.php">All Campaigns</a></li>
          <?php endif; ?>

          <li><a href="<?= BASE_URL ?>public/logout.php">Log Out</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
  <main>
