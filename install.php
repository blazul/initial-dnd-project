<?php
// install.php

// If already installed, redirect
if (file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: public/index.php');
    exit;
}

$errors = [];
$done   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather & trim inputs
    $dbHost    = trim($_POST['db_host']    ?? '');
    $dbName    = trim($_POST['db_name']    ?? '');
    $dbUser    = trim($_POST['db_user']    ?? '');
    $dbPass    = trim($_POST['db_pass']    ?? '');
    $tblPref   = trim($_POST['tbl_prefix'] ?? '');

    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass = $_POST['admin_pass']      ?? '';
    $adminConf = $_POST['admin_conf']      ?? '';

    $userUser  = trim($_POST['user_user']  ?? '');
    $userPass  = $_POST['user_pass']       ?? '';
    $userConf  = $_POST['user_conf']       ?? '';

    // Validate required fields
    if (!$dbHost || !$dbName || !$dbUser || !$tblPref) {
        $errors[] = 'Wypełnij wszystkie pola konfiguracji bazy (host, baza, użytkownik, prefiks).';
    }
    if (!$adminUser || !$adminPass) {
        $errors[] = 'Podaj login i hasło administratora.';
    } elseif ($adminPass !== $adminConf) {
        $errors[] = 'Hasło administratora i jego potwierdzenie nie zgadzają się.';
    }
    if (!$userUser || !$userPass) {
        $errors[] = 'Podaj login i hasło domyślnego użytkownika.';
    } elseif ($userPass !== $userConf) {
        $errors[] = 'Hasło użytkownika i jego potwierdzenie nie zgadzają się.';
    } elseif ($userUser === $adminUser) {
        $errors[] = 'Login domyślnego użytkownika nie może być taki sam jak login administratora.';
    }

    if (empty($errors)) {
        // Write includes/config.php
        $cfg = <<<PHP
<?php
define('DB_HOST',  '$dbHost');
define('DB_NAME',  '$dbName');
define('DB_USER',  '$dbUser');
define('DB_PASS',  '$dbPass');
define('DB_PREFIX','$tblPref');
define('APP_ENV', 'production');
PHP;

        if (!is_dir(__DIR__ . '/includes') || !is_writable(__DIR__ . '/includes')) {
            $errors[] = 'Brak uprawnień do folderu includes/.';
        } elseif (file_put_contents(__DIR__ . '/includes/config.php', $cfg) === false) {
            $errors[] = 'Nie udało się zapisać includes/config.php.';
        } else {
            // Initialize DB
            try {
                $dsn = "mysql:host={$dbHost};dbname={$dbName}";
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                // Load and run schema
                $prefix = $tblPref;
                require __DIR__ . '/sql/sql.php'; // defines $create[]
                foreach ($create as $sql) {
                    $pdo->exec($sql);
                }

                // Insert initial users
                $hashAdmin = password_hash($adminPass, PASSWORD_DEFAULT);
                $hashUser  = password_hash($userPass,  PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO `{$tblPref}users`
                      (username, email, password, role)
                    VALUES
                      (:au, :ae, :ap, 'admin'),
                      (:uu, :ue, :up, 'user')
                ");
                $stmt->execute([
                    'au' => $adminUser,
                    'ae' => $adminUser . '@example.com',
                    'ap' => $hashAdmin,
                    'uu' => $userUser,
                    'ue' => $userUser  . '@example.com',
                    'up' => $hashUser,
                ]);

                $done = true;
            } catch (Exception $e) {
                $errors[] = 'Błąd bazy danych: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Instalator DnD Manager</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; }
    h1, h2 { margin-bottom: .5rem; }
    label { display: block; margin: .5rem 0; }
    input { width: 100%; padding: .5rem; margin-bottom: 1rem; }
    button { padding: .6rem 1.2rem; }
    .errors { background: #fee; padding: 1rem; border: 1px solid #f99; margin-bottom: 1rem; }
    .success { background: #efe; padding: 1rem; border: 1px solid #9f9; }
  </style>
</head>
<body>
  <h1>Instalator DnD Manager</h1>

  <?php if ($done): ?>
    <div class="success">
      ✅ Instalacja zakończona!<br>
      <a href="public/index.php">Przejdź do aplikacji</a>
    </div>
  <?php else: ?>
    <?php if ($errors): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post">
      <h2>Konfiguracja bazy danych</h2>
      <label>Host MySQL
        <input type="text" name="db_host"    value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
      </label>
      <label>Nazwa bazy
        <input type="text" name="db_name"    value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
      </label>
      <label>Użytkownik DB
        <input type="text" name="db_user"    value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
      </label>
      <label>Hasło DB
        <input type="password" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
      </label>
      <label>Prefiks tabel
        <input type="text" name="tbl_prefix" value="<?= htmlspecialchars($_POST['tbl_prefix'] ?? 'app_') ?>" required>
      </label>

      <h2>Konto administratora</h2>
      <label>Login
        <input type="text" name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? '') ?>" required>
      </label>
      <label>Hasło
        <input type="password" name="admin_pass" required>
      </label>
      <label>Potwierdź hasło
        <input type="password" name="admin_conf" required>
      </label>

      <h2>Domyślny użytkownik</h2>
      <label>Login
        <input type="text" name="user_user" value="<?= htmlspecialchars($_POST['user_user'] ?? '') ?>" required>
      </label>
      <label>Hasło
        <input type="password" name="user_pass" required>
      </label>
      <label>Potwierdź hasło
        <input type="password" name="user_conf" required>
      </label>

      <button type="submit">Zainstaluj</button>
    </form>
  <?php endif; ?>
</body>
</html>
