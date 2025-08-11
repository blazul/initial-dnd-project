<?php
// includes/config.php
require_once __DIR__ . '/url.php';

define('DB_HOST',       '[[DB_HOST]]');
define('DB_NAME',       '[[DB_NAME]]');
define('DB_USER',       '[[DB_USER]]');
define('DB_PASS',       '[[DB_PASS]]');
define('TABLE_PREFIX',  '[[TABLE_PREFIX]]');  
define('APP_ENV',       'production');
define('BASE_URL',      '[[BASE_URL]]');      

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

if (!isset($GLOBALS['pdo']) || !($GLOBALS['pdo'] instanceof PDO)) {
    $GLOBALS['pdo'] = get_pdo();
}

function table(string $name): string {
    return TABLE_PREFIX . $name;
}
