<?php
// install.php

// if already installed, redirect
if (file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: public/index.php');
    exit;
}

require_once __DIR__ . '/includes/url.php';  // <-- pulls in url_origin() + full_url()

$errors = [];
$done   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim inputs
    $dbHost    = trim($_POST['db_host']   ?? '');
    $dbName    = trim($_POST['db_name']   ?? '');
    $dbUser    = trim($_POST['db_user']   ?? '');
    $dbPass    = trim($_POST['db_pass']   ?? '');
    $tblPref   = trim($_POST['tbl_prefix']?? '');
    $adminUser = trim($_POST['admin_user']?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminPass2= $_POST['admin_pass2']?? '';

    // validation
    if (!$dbHost || !$dbName || !$dbUser) {
        $errors[] = 'Host, nazwa bazy i użytkownik DB są wymagane.';
    }
    if (!$tblPref) {
        $errors[] = 'Prefiks tabel jest wymagany.';
    }
    if (!$adminUser) {
        $errors[] = 'Login administratora jest wymagany.';
    }
    if (!$adminPass || $adminPass !== $adminPass2) {
        $errors[] = 'Hasła administratora są puste lub niezgodne.';
    }

    if (empty($errors)) {
        // detect base URL
        $baseUrl = rtrim(pathinfo(full_url($_SERVER), PATHINFO_DIRNAME), '/') . '/';

        // build config.php content
        $cfg = <<<PHP
<?php
// includes/config.php

require_once __DIR__ . '/url.php';

define('DB_HOST',    '$dbHost');
define('DB_NAME',    '$dbName');
define('DB_USER',    '$dbUser');
define('DB_PASS',    '$dbPass');

define('TABLE_PREFIX', '$tblPref');
define('APP_ENV', 'production');

define('BASE_URL', '$baseUrl');
PHP;

        // write config.php
        if (!is_dir(__DIR__ . '/includes') || !is_writable(__DIR__ . '/includes')) {
            $errors[] = 'Brak uprawnień do folderu includes/.';
        }
        elseif (file_put_contents(__DIR__ . '/includes/config.php', $cfg) === false) {
            $errors[] = 'Nie udało się zapisać includes/config.php';
        }
        else {
            // import schema + create admin
            try {
                require __DIR__ . '/includes/config.php';
                require __DIR__ . '/sql/sql.php';

                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);

                // run each CREATE TABLE
                foreach ($create as $sql) {
                    $pdo->exec(str_replace('{$prefix}', TABLE_PREFIX, $sql));
                }

                // insert admin
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                  INSERT INTO `".TABLE_PREFIX."users`
                    (username, email, password, role)
                  VALUES
                    (:u, :e, :p, 'admin')
                ");
                $stmt->execute([
                    'u' => $adminUser,
                    'e' => $adminUser . '@example.local',
                    'p' => $hash,
                ]);

                $done = true;
            } catch (Exception $e) {
                $errors[] = 'Błąd bazy: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Instalacja DnD Manager</title>
  <style>
    body{font-family:sans-serif;padding:2rem;max-width:500px;margin:auto;}
    label{display:block;margin:0.5rem 0 0.2rem;}
    input{width:100%;padding:0.4rem;margin-bottom:1rem;}
    button{padding:0.6rem 1.2rem;margin-top:1rem;}
    .error{background:#fee;padding:1rem;border:1px solid #f99;margin-bottom:1rem;}
    .success{background:#efe;padding:1rem;border:1px solid #9f9;margin-bottom:1rem;}
    hr{margin:1.5rem 0;}
  </style>
</head>
<body>

<h1>Instalacja DnD Manager</h1>

<?php if ($done): ?>
  <div class="success">
    ✅ Instalacja zakończona!<br>
    <a href="public/index.php">Przejdź do aplikacji</a>
  </div>
<?php else: ?>
  <?php if ($errors): ?>
    <div class="error">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?=htmlspecialchars($e)?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <label>Host MySQL
      <input type="text" name="db_host" value="<?=htmlspecialchars($_POST['db_host']??'localhost')?>" required>
    </label>
    <label>Nazwa bazy
      <input type="text" name="db_name" value="<?=htmlspecialchars($_POST['db_name']??'')?>" required>
    </label>
    <label>Użytkownik DB
      <input type="text" name="db_user" value="<?=htmlspecialchars($_POST['db_user']??'')?>" required>
    </label>
    <label>Hasło DB
      <input type="password" name="db_pass" value="<?=htmlspecialchars($_POST['db_pass']??'')?>">
    </label>
    <label>Prefiks tabel
      <input type="text" name="tbl_prefix" value="<?=htmlspecialchars($_POST['tbl_prefix']??'app_')?>" required>
    </label>

    <hr>

    <label>Administrator – login
      <input type="text" name="admin_user" value="<?=htmlspecialchars($_POST['admin_user']??'')?>" required>
    </label>
    <label>Administrator – hasło
      <input type="password" name="admin_pass" required>
    </label>
    <label>Powtórz hasło
      <input type="password" name="admin_pass2" required>
    </label>

    <button type="submit">Zainstaluj</button>
  </form>
<?php endif; ?>

</body>
</html>
