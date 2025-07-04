<?php
// sql/insert.php

$insert = [];

// create default admin user
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$insert[] = "
INSERT INTO `{$prefix}users`
  (`username`,`email`,`password`,`role`,`is_active`)
VALUES
  ('admin','admin@example.com','$adminPass','admin',1);
";