<?php
declare(strict_types=1);
// Run locally only: php tools/password_hash.php
fwrite(STDOUT, "Enter password: ");
$password = trim((string)fgets(STDIN));
if ($password === '') { fwrite(STDERR, "Password cannot be empty.\n"); exit(1); }
fwrite(STDOUT, password_hash($password, PASSWORD_DEFAULT) . PHP_EOL);
