<?php
declare(strict_types=1);

function validate_date(string $date): ?string { $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date); return $d && $d->format('Y-m-d') === $date ? $date : null; }
function validate_time(string $time): ?string { return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) ? $time : null; }
function validate_reading(string $systolic, string $diastolic, string $pulse = ''): array {
    if ($systolic === '' || $diastolic === '' || $pulse === '' || !ctype_digit($systolic) || !ctype_digit($diastolic) || !ctype_digit($pulse)) return [null, 'Enter numeric systolic, diastolic, and pulse values.'];
    $s = (int)$systolic; $d = (int)$diastolic; $p=(int)$pulse;
    if ($s < BP_MIN || $s > BP_MAX || $d < BP_MIN || $d > BP_MAX || $s <= $d) return [null, 'Enter sensible BP values (systolic must be higher than diastolic).'];
    if ($p < 25 || $p > 250) return [null, 'Enter a sensible pulse value.'];
    return [['systolic' => $s, 'diastolic' => $d, 'pulse' => $p], null];
}
