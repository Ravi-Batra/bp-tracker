<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php'; require __DIR__ . '/lib/auth.php'; require __DIR__ . '/lib/validation.php'; require __DIR__ . '/lib/storage.php'; require __DIR__ . '/lib/bp.php';
start_secure_session(); require_login(); require_recorder();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) { flash('error', 'Your session expired. Please try again.'); header('Location: index.php'); exit; }
$date = validate_date((string)($_POST['date'] ?? '')); $period = $_POST['period'] ?? ''; $slot = (int)($_POST['slot'] ?? 0); $edit = ($_POST['edit'] ?? '') === '1'; $time=validate_time((string)($_POST['time'] ?? ''));
[$reading, $error] = validate_reading((string)($_POST['systolic'] ?? ''), (string)($_POST['diastolic'] ?? ''), (string)($_POST['pulse'] ?? ''));
if (!$date || !$time || !in_array($period, ['morning', 'evening'], true) || !in_array($slot,[1,2],true) || $error) { flash('error', $error ?? 'Enter a valid date, time, and period.'); header('Location: index.php'); exit; }
$originalDate = validate_date((string)($_POST['original_date'] ?? ''));
$originalPeriod = (string)($_POST['original_period'] ?? '');
$originalSlot = (int)($_POST['original_slot'] ?? 0);
$reading['time'] = $time;
try {
    save_reading($date, $period, $slot, $reading, $edit, $originalDate, $originalPeriod, $originalSlot);
    flash('success', ucfirst($period) . ' reading saved.');
} catch (RuntimeException $e) {
    error_log('BP Tracker: ' . $e->getMessage());
    flash('error', $e->getMessage());
}
header('Location: index.php');
