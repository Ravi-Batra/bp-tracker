<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php'; require __DIR__ . '/lib/auth.php';
start_secure_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) { flash('error', 'Your session expired. Please try again.'); header('Location: index.php'); exit; }
$role = $_POST['role'] ?? ''; $password = (string)($_POST['password'] ?? '');
$configuredPassword = $role === 'recorder' ? RECORDER_PASSWORD : ($role === 'viewer' ? VIEWER_PASSWORD : '');
if ($configuredPassword === '' || !hash_equals($configuredPassword, $password)) { flash('error', 'Login failed. Please check the selected mode and password.'); header('Location: index.php'); exit; }
login_as($role); flash('success', 'Signed in successfully.'); header('Location: index.php');
