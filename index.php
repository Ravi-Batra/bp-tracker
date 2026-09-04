<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/storage.php';
require __DIR__ . '/lib/bp.php';
start_secure_session();
$protectionEnabled = password_protection_enabled();
$role = logged_in_role();
$flash = pull_flash();
$records = [];
try { if ($role) $records = table_records(); } catch (RuntimeException $e) { error_log('BP Tracker: '.$e->getMessage()); $flash=['type'=>'error','message'=>'The saved readings are temporarily unavailable.']; }
if ($role === 'recorder' && !$records) $records = [['date'=>date('Y-m-d'),'morning'=>[1=>null,2=>null],'evening'=>[1=>null,2=>null]]];
function h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function display_date(string $date): string { return (new DateTimeImmutable($date))->format('d-m-Y'); }
function reading_html(?array $reading, string $date, string $period, int $slot, bool $editable): string {
    if (!$reading) {
        if (!$editable) return '<span class="missing">—</span>';
        return '<button class="reading-action add" type="button" data-date="'.h($date).'" data-period="'.h($period).'" data-slot="'.$slot.'">+ Add</button>';
    }
    $text='<span class="bp-value">'.h($reading['systolic']).' / '.h($reading['diastolic']).'</span><span class="reading-meta">Pulse '.h($reading['pulse'] ?? '—').' · '.h($reading['time'] ?? '—').'</span>';
    if ($editable) $text.='<button class="reading-action edit" type="button" data-edit="1" data-date="'.h($date).'" data-period="'.h($period).'" data-slot="'.$slot.'" data-s="'.h($reading['systolic']).'" data-d="'.h($reading['diastolic']).'" data-pulse="'.h($reading['pulse'] ?? '').'" data-time="'.h($reading['time'] ?? '').'">Edit</button>';
    return $text;
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h(APP_NAME)?></title><link rel="stylesheet" href="assets/style.css?v=20260903"></head><body><main class="shell"><header><h1>BP Tracker</h1><p>Simple blood pressure record</p></header>
<?php if($flash):?><div class="notice <?=h($flash['type'])?>" role="alert"><?=h($flash['message'])?></div><?php endif;?>
<div class="mode-row"><?php if($role):?><strong class="mode <?=h($role)?>"><?=$protectionEnabled?strtoupper(h($role)):'OPEN ACCESS'?></strong><?php else:?><span></span><?php endif;?><span><?php if($protectionEnabled&&$role):?><a class="logout" href="logout.php">Log out</a> · <?php endif;?><a class="logout" href="settings.php">Settings</a></span></div>
<?php if(!$role):?><section class="card login-card"><h2>Sign in</h2><p>Select how you want to use the tracker.</p><form action="login.php" method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><fieldset><legend>Access mode</legend><label class="choice"><input type="radio" name="role" value="recorder" checked> Recorder <small>Add and edit readings</small></label><label class="choice"><input type="radio" name="role" value="viewer"> Viewer <small>View readings only</small></label></fieldset><label>Password<input name="password" type="password" required></label><button class="primary">Sign in</button></form></section>
<?php else:?>
<?php if($role==='recorder'):?><div id="entry-form-parking" hidden><form class="inline-entry-form" id="entry-form" action="api.php" method="post" hidden><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input name="date" type="hidden"><input name="period" type="hidden"><input name="slot" type="hidden"><input name="edit" type="hidden" value="0"><input name="original_date" type="hidden"><input name="original_period" type="hidden"><input name="original_slot" type="hidden"><div class="inline-bp-fields"><label>Systolic<input name="systolic" inputmode="numeric" pattern="[0-9]*" maxlength="3" required></label><span class="bp-divider">/</span><label>Diastolic<input name="diastolic" inputmode="numeric" pattern="[0-9]*" maxlength="3" required></label></div><div class="inline-meta-fields"><label>Pulse<input name="pulse" inputmode="numeric" pattern="[0-9]*" maxlength="3" required></label><label>Time<input name="time" type="text" inputmode="numeric" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" placeholder="HH:MM" required></label></div><div class="inline-form-actions"><button class="primary" id="save-button">Save</button><button class="secondary cancel-entry" type="button">Cancel</button></div></form></div><?php endif;?>
<section class="card report-controls"><h2>Doctor report</h2><p>Select a date range, then print or save it as a PDF.</p><form action="report.php" method="get" target="_blank"><div class="report-date-fields"><label>From<input name="from" type="date" value="<?=(new DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d')?>" required></label><label>To<input name="to" type="date" value="<?=(new DateTimeImmutable('today'))->format('Y-m-d')?>" required></label></div><button class="primary">Open doctor report</button></form></section>
<section class="card table-card"><h2>Readings</h2><?php if(!$records):?><p class="empty">No BP readings recorded yet.</p><?php else:?><div class="reading-list"><?php foreach($records as $record):?><article class="day-row"><div class="day-date"><?=h(display_date($record['date']))?></div><div class="period-column"><strong>Morning</strong><?php for($slot=1;$slot<=2;$slot++):?><div class="reading-item"><span class="slot-label"><?=$slot?></span><div class="reading-content"><?=reading_html($record['morning'][$slot]??null,$record['date'],'morning',$slot,$role==='recorder')?></div></div><?php endfor;?></div><div class="period-column"><strong>Evening</strong><?php for($slot=1;$slot<=2;$slot++):?><div class="reading-item"><span class="slot-label"><?=$slot?></span><div class="reading-content"><?=reading_html($record['evening'][$slot]??null,$record['date'],'evening',$slot,$role==='recorder')?></div></div><?php endfor;?></div></article><?php endforeach;?></div><?php endif;?></section><?php endif;?></main><script src="assets/app.js?v=20260903"></script></body></html>
