<?php
declare(strict_types=1);

$configCandidates = [
    dirname(__DIR__) . '/bp-tracker-private/config.php',
    __DIR__ . '/config/config.php',
];

$configFile = null;
foreach ($configCandidates as $candidate) {
    if (is_file($candidate)) {
        $configFile = $candidate;
        break;
    }
}

if ($configFile === null) {
    throw new RuntimeException('BP Tracker configuration is unavailable.');
}

require $configFile;
unset($configCandidates, $configFile, $candidate);
