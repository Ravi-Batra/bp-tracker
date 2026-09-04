<?php
declare(strict_types=1);

const DATA_FILE = __DIR__ . '/smoke-data.json';
const BP_MIN = 30;
const BP_MAX = 300;
require dirname(__DIR__) . '/lib/storage.php';
require dirname(__DIR__) . '/lib/validation.php';
require dirname(__DIR__) . '/lib/bp.php';

function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    echo 'PASS: ' . $message . PHP_EOL;
}

try {
    file_put_contents(DATA_FILE, json_encode(['records' => [['date' => '2026-08-20', 'morning' => ['systolic' => 120, 'diastolic' => 80], 'evening' => null]]], JSON_PRETTY_PRINT));
    $legacy = all_records();
    check(($legacy[0]['morning'][1]['systolic'] ?? null) === 120, 'legacy single reading becomes Morning 1');

    file_put_contents(DATA_FILE, json_encode(['records' => []], JSON_PRETTY_PRINT));
    [$valid, $error] = validate_reading('120', '80', '72');
    check($error === null && $valid['pulse'] === 72, 'pulse is required and accepted');
    check(validate_time('07:35') === '07:35' && validate_time('25:00') === null, 'time validation');

    $date = '2026-08-29';
    save_reading($date, 'morning', 1, $valid + ['time' => '07:35'], false);
    save_reading($date, 'morning', 2, ['systolic' => 122, 'diastolic' => 81, 'pulse' => 73, 'time' => '07:40'], false);
    save_reading($date, 'evening', 1, ['systolic' => 124, 'diastolic' => 82, 'pulse' => 74, 'time' => '20:10'], false);
    save_reading($date, 'evening', 2, ['systolic' => 125, 'diastolic' => 83, 'pulse' => 75, 'time' => '20:15'], false);
    check(count(array_filter(all_records()[0]['morning'])) === 2 && count(array_filter(all_records()[0]['evening'])) === 2, 'two Morning and two Evening readings');

    $duplicateBlocked = false;
    try { save_reading($date, 'morning', 2, $valid + ['time' => '08:00'], false); } catch (RuntimeException) { $duplicateBlocked = true; }
    check($duplicateBlocked, 'duplicate slot blocked');

    save_reading('2026-08-28', 'evening', 2, ['systolic' => 118, 'diastolic' => 76, 'pulse' => 70, 'time' => '19:45'], true, $date, 'morning', 1);
    $records = all_records();
    check($records[0]['date'] === '2026-08-29' && $records[1]['date'] === '2026-08-28', 'back-date edit reorders newest first');
    check(($records[1]['evening'][2]['time'] ?? null) === '19:45', 'date, period, slot, and time edit');
} finally {
    if (file_exists(DATA_FILE)) unlink(DATA_FILE);
}
