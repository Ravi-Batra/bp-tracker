<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/auth.php';
start_secure_session();

function settings_h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$message = null;
$messageType = 'error';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Your session expired. Please try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'owner_login') {
            if (verify_owner_password((string)($_POST['owner_password'] ?? ''))) {
                login_owner();
                header('Location: settings.php');
                exit;
            }
            $message = 'Owner password is incorrect.';
        } elseif ($action === 'owner_logout') {
            logout_owner();
            header('Location: settings.php');
            exit;
        } elseif ($action === 'save_setting' && owner_is_authenticated()) {
            try {
                set_password_protection(($_POST['password_protection'] ?? '') === '1');
                $message = 'Password Protection setting saved.';
                $messageType = 'success';
            } catch (RuntimeException $e) {
                error_log('BP Tracker settings: ' . $e->getMessage());
                $message = 'The setting could not be saved.';
            }
        } else {
            $message = 'Owner authorization is required.';
        }
    }
}
$enabled = password_protection_enabled();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Settings - <?=settings_h(APP_NAME)?></title><link rel="stylesheet" href="assets/style.css?v=20260830"></head><body><main class="shell"><header><h1>Settings</h1><p>Owner controls</p></header>
<?php if($message):?><div class="notice <?=settings_h($messageType)?>" role="alert"><?=settings_h($message)?></div><?php endif;?>
<?php if(!owner_is_authenticated()):?><section class="card login-card"><h2>Owner access</h2><p>Enter the separate owner password to change Password Protection.</p><form method="post"><input type="hidden" name="csrf" value="<?=settings_h(csrf_token())?>"><input type="hidden" name="action" value="owner_login"><label>Owner password<input name="owner_password" type="password" required autocomplete="current-password"></label><button class="primary">Open settings</button></form></section>
<?php else:?><section class="card"><h2>Password Protection</h2><p>Current status: <strong><?=$enabled?'ON':'OFF'?></strong></p><form method="post"><input type="hidden" name="csrf" value="<?=settings_h(csrf_token())?>"><input type="hidden" name="action" value="save_setting"><label class="choice"><input type="radio" name="password_protection" value="0" <?=$enabled?'':'checked'?>> OFF <small>Open the tracker without login, with add and edit access.</small></label><label class="choice"><input type="radio" name="password_protection" value="1" <?=$enabled?'checked':''?>> ON <small>Require the existing Recorder or Viewer password.</small></label><button class="primary">Save setting</button></form><form method="post"><input type="hidden" name="csrf" value="<?=settings_h(csrf_token())?>"><input type="hidden" name="action" value="owner_logout"><button class="secondary">Lock owner settings</button></form></section><?php endif;?>
<p><a class="logout" href="index.php">Back to tracker</a></p></main></body></html>
