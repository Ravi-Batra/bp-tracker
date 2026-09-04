<?php
declare(strict_types=1);

function normalize_period(mixed $value): array {
    if (is_array($value) && isset($value['systolic'])) return [1 => $value, 2 => null];
    if (!is_array($value)) return [1 => null, 2 => null];
    return [1 => $value[1] ?? null, 2 => $value[2] ?? null];
}

function normalize_record(array $record): array {
    return [
        'date' => (string)($record['date'] ?? ''),
        'morning' => normalize_period($record['morning'] ?? null),
        'evening' => normalize_period($record['evening'] ?? null),
    ];
}

function all_records(): array {
    $records = array_map('normalize_record', read_data()['records']);
    usort($records, fn(array $a, array $b): int => strcmp($b['date'], $a['date']));
    return $records;
}

function save_reading(string $date, string $period, int $slot, array $reading, bool $isEdit, ?string $originalDate = null, ?string $originalPeriod = null, ?int $originalSlot = null): void {
    update_data(function (array &$data) use ($date, $period, $slot, $reading, $isEdit, $originalDate, $originalPeriod, $originalSlot): void {
        $records = array_map('normalize_record', $data['records']);
        $sourceDate = $originalDate ?: $date;
        $sourcePeriod = $originalPeriod ?: $period;
        $sourceSlot = $originalSlot ?: $slot;
        $sourceIndex = null;
        $targetIndex = null;

        foreach ($records as $index => $record) {
            if ($record['date'] === $sourceDate) $sourceIndex = $index;
            if ($record['date'] === $date) $targetIndex = $index;
        }

        if ($isEdit) {
            if ($sourceIndex === null || empty($records[$sourceIndex][$sourcePeriod][$sourceSlot])) throw new RuntimeException('Reading not found for editing.');
            $sameSlot = $sourceDate === $date && $sourcePeriod === $period && $sourceSlot === $slot;
            if (!$sameSlot && $targetIndex !== null && !empty($records[$targetIndex][$period][$slot])) throw new RuntimeException('That destination reading already exists.');
            $records[$sourceIndex][$sourcePeriod][$sourceSlot] = null;
        } elseif ($targetIndex !== null && !empty($records[$targetIndex][$period][$slot])) {
            throw new RuntimeException(ucfirst($period) . ' ' . $slot . ' already exists. Please edit it instead.');
        }

        if ($targetIndex === null) {
            $records[] = ['date' => $date, 'morning' => [1 => null, 2 => null], 'evening' => [1 => null, 2 => null]];
            $targetIndex = array_key_last($records);
        }
        $records[$targetIndex][$period][$slot] = $reading;
        $data['records'] = array_values(array_filter($records, fn(array $record): bool => array_filter($record['morning']) || array_filter($record['evening'])));
    });
}

function table_records(): array {
    $records = all_records();
    if (!$records) return [];
    $today = new DateTimeImmutable('today');
    $cutoff = $today->modify('-29 days')->format('Y-m-d');
    $first = min(array_column($records, 'date'));
    $start = max($cutoff, $first);
    $days = [];
    for ($day = $today; $day->format('Y-m-d') >= $start; $day = $day->modify('-1 day')) {
        $days[$day->format('Y-m-d')] = ['date' => $day->format('Y-m-d'), 'morning' => [1 => null, 2 => null], 'evening' => [1 => null, 2 => null]];
    }
    foreach ($records as $record) if (isset($days[$record['date']])) $days[$record['date']] = $record;
    return array_values($days);
}
