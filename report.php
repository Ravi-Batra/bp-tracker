<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/storage.php';
require __DIR__ . '/lib/bp.php';
start_secure_session();
require_login();

function report_h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function report_date_is_valid(string $date): bool {
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}
function report_cell(?array $reading): string {
    if (!$reading) return '<span class="report-missing">—</span>';
    return '<strong>'.report_h($reading['systolic'] ?? '—').' / '.report_h($reading['diastolic'] ?? '—').'</strong>'
        .'<small>Pulse '.report_h($reading['pulse'] ?? '—').' · '.report_h($reading['time'] ?? '—').'</small>';
}

$today = new DateTimeImmutable('today');
$from = (string)($_GET['from'] ?? $today->modify('-6 days')->format('Y-m-d'));
$to = (string)($_GET['to'] ?? $today->format('Y-m-d'));
$error = null;
$records = [];
$fromLabel = report_date_is_valid($from) ? (new DateTimeImmutable($from))->format('d-m-Y') : $from;
$toLabel = report_date_is_valid($to) ? (new DateTimeImmutable($to))->format('d-m-Y') : $to;

if (!report_date_is_valid($from) || !report_date_is_valid($to)) {
    $error = 'Please select valid From and To dates.';
} elseif ($from > $to) {
    $error = 'The From date must not be later than the To date.';
} else {
    try {
        $records = array_values(array_filter(all_records(), fn(array $record): bool => $record['date'] >= $from && $record['date'] <= $to));
    } catch (RuntimeException $e) {
        error_log('BP Tracker report: '.$e->getMessage());
        $error = 'The saved readings are temporarily unavailable.';
    }
}

$readings = [];
foreach ($records as $record) {
    foreach (['morning', 'evening'] as $period) {
        foreach ([1, 2] as $slot) {
            $reading = $record[$period][$slot] ?? null;
            if ($reading) $readings[] = $reading;
        }
    }
}
function report_average(array $readings, string $field): string {
    $values = array_values(array_filter(array_map(fn(array $reading): ?float => isset($reading[$field]) && is_numeric($reading[$field]) ? (float)$reading[$field] : null, $readings), fn(?float $value): bool => $value !== null));
    return $values ? number_format(array_sum($values) / count($values), 1) : '—';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Doctor report - <?=report_h(APP_NAME)?></title><link rel="stylesheet" href="assets/style.css?v=20260830"><link rel="stylesheet" href="assets/report-print.css?v=1" media="print"></head><body><main class="report-shell"><div class="report-actions"><a class="secondary report-back" href="index.php">Back to tracker</a><button class="primary report-print" type="button" onclick="window.print()">Print / Save as PDF</button></div><header class="report-header"><h1>Blood Pressure Report</h1><p><?=report_h($fromLabel)?> to <?=report_h($toLabel)?></p></header>
<?php if($error):?><div class="notice error" role="alert"><?=report_h($error)?></div><?php else:?><section class="report-summary"><div><span>Readings</span><strong><?=count($readings)?></strong></div><div><span>Average systolic (mmHg)</span><strong><?=report_h(report_average($readings, 'systolic'))?></strong></div><div><span>Average diastolic (mmHg)</span><strong><?=report_h(report_average($readings, 'diastolic'))?></strong></div><div><span>Average pulse (bpm)</span><strong><?=report_h(report_average($readings, 'pulse'))?></strong></div></section>
<section class="report-table-wrap"><?php if(!$records):?><p class="report-empty">No readings were recorded in this date range.</p><?php else:?><table class="report-table"><thead><tr><th>Date</th><th>Morning 1</th><th>Morning 2</th><th>Evening 1</th><th>Evening 2</th></tr></thead><tbody><?php foreach($records as $record):?><tr><th><?=report_h((new DateTimeImmutable($record['date']))->format('d-m-Y'))?></th><td><?=report_cell($record['morning'][1]??null)?></td><td><?=report_cell($record['morning'][2]??null)?></td><td><?=report_cell($record['evening'][1]??null)?></td><td><?=report_cell($record['evening'][2]??null)?></td></tr><?php endforeach;?></tbody></table><?php endif;?></section><?php endif;?>
<p class="report-note">This report presents recorded home measurements only. Your healthcare professional should interpret the results.</p></main></body></html>
