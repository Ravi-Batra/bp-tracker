<?php
declare(strict_types=1);

function read_data(): array {
    if (!file_exists(DATA_FILE)) return ['records' => []];
    $raw = file_get_contents(DATA_FILE); $data = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($data) || !isset($data['records']) || !is_array($data['records'])) throw new RuntimeException('Stored data is unavailable.');
    return $data;
}
function update_data(callable $change): void {
    $dir = dirname(DATA_FILE); if (!is_dir($dir) && !mkdir($dir, 0750, true)) throw new RuntimeException('Storage is unavailable.');
    $handle = fopen(DATA_FILE, 'c+'); if (!$handle || !flock($handle, LOCK_EX)) throw new RuntimeException('Could not lock data storage.');
    try { rewind($handle); $raw = stream_get_contents($handle); $data = $raw === '' ? ['records' => []] : json_decode($raw, true); if (!is_array($data) || !isset($data['records']) || !is_array($data['records'])) throw new RuntimeException('Stored data is invalid.'); $change($data); $json = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR); ftruncate($handle, 0); rewind($handle); fwrite($handle, $json . PHP_EOL); fflush($handle); } finally { flock($handle, LOCK_UN); fclose($handle); }
}
