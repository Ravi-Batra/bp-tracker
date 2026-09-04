<?php
declare(strict_types=1);
require __DIR__ . '/lib/auth.php'; start_secure_session(); logout_user(); header('Location: index.php');
