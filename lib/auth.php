<?php
declare(strict_types=1);

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('bp_tracker_session');
        session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
        session_start();
    }
}

function password_protection_file(): string { return dirname(DATA_FILE) . '/settings.json'; }
function password_protection_enabled(): bool {
    $file = password_protection_file();
    if (!is_file($file)) return false;
    $raw = file_get_contents($file);
    $settings = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($settings) || !array_key_exists('password_protection', $settings)) return true;
    return $settings['password_protection'] === true;
}
function set_password_protection(bool $enabled): void {
    $file = password_protection_file();
    $directory = dirname($file);
    if (!is_dir($directory) && !mkdir($directory, 0750, true)) throw new RuntimeException('Settings storage is unavailable.');
    $handle = fopen($file, 'c+');
    if (!$handle || !flock($handle, LOCK_EX)) throw new RuntimeException('Could not lock settings storage.');
    try {
        $json = json_encode(['password_protection' => $enabled], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) throw new RuntimeException('Could not encode settings.');
        rewind($handle);
        if (!ftruncate($handle, 0) || fwrite($handle, $json . PHP_EOL) === false || !fflush($handle)) throw new RuntimeException('Could not save settings.');
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
function logged_in_role(): ?string { return password_protection_enabled() ? ($_SESSION['role'] ?? null) : 'recorder'; }
function is_recorder(): bool { return logged_in_role() === 'recorder'; }
function require_login(): void { if (!logged_in_role()) { header('Location: index.php'); exit; } }
function require_recorder(): void { if (!is_recorder()) { http_response_code(403); exit('Not authorized.'); } }
function login_as(string $role): void { session_regenerate_id(true); $_SESSION['role'] = $role; }
function owner_is_authenticated(): bool { return (int)($_SESSION['owner_authenticated_until'] ?? 0) >= time(); }
function verify_owner_password(string $password): bool {
    return defined('OWNER_PASSWORD_HASH') && OWNER_PASSWORD_HASH !== '' && password_verify($password, OWNER_PASSWORD_HASH);
}
function login_owner(): void { session_regenerate_id(true); $_SESSION['owner_authenticated_until'] = time() + 900; }
function logout_owner(): void { unset($_SESSION['owner_authenticated_until']); }
function logout_user(): void { $_SESSION = []; if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']); } session_destroy(); }
function csrf_token(): string { if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); } return $_SESSION['csrf']; }
function verify_csrf(): bool { return isset($_POST['csrf']) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']); }
function flash(string $type, string $message): void { $_SESSION['flash'] = ['type' => $type, 'message' => $message]; }
function pull_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
