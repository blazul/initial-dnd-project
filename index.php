<?php
// Redirect all root requests into /public
header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/public/index.php');
exit;
