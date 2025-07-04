<?php
// includes/header.php

// 1) Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Load BASE_URL and anything else
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'DnD Manager') ?></title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QFY6qcfOWmT2rDB1pPvrTDm+J9ZNW0mO/pXuXqGd8P4p8kFZO1t2mEnv9K6JYtY+"
    crossorigin="anonymous"
  >

  <!-- Custom styles -->
  <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>
  <header class="bg-light mb-4">
    <nav class="navbar navbar-expand-lg navbar-light container">
      <!-- left spacer for perfect centering on desktop -->
      <div class="collapse navbar-collapse order-1 order-lg-0">
        <ul class="navbar-nav"></ul>
      </div>

      <!-- brand centered -->
      <a class="navbar-brand mx-auto order-0 order-lg-1" href="<?= BASE_URL ?>public/index.php">
        DnD Manager
      </a>

      <!-- right nav links -->
      <div class="collapse navbar-collapse justify-content-end order-2" id="mainNav">
        <ul class="navbar-nav">
          <?php if (! empty($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a class="nav-link text-dark" href="<?= BASE_URL ?>friends/send.php">
                Friends
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="<?= BASE_URL ?>public/logout.php">
                Logout (<?= htmlspecialchars($_SESSION['username'] ?? '') ?>)
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASE_URL ?>public/login.php">Log In</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASE_URL ?>public/register.php">Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- mobile toggle button -->
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-controls="mainNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
    </nav>
  </header>

  <main class="container">
