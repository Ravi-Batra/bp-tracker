<?php
declare(strict_types=1);

// Generate this with tools/password_hash.php. Never put the plain owner password here.
const OWNER_PASSWORD_HASH = 'PASTE_OWNER_PASSWORD_HASH_HERE';

/*
 * Local: copy this file to config/config.php and keep the DATA_FILE line below.
 * Hostinger: copy it to bp-tracker-private/config.php outside public_html and
 * change DATA_FILE to: __DIR__ . '/bp.json'
 * Keep config.php private and never upload it to Git.
 */
const RECORDER_PASSWORD = 'CHANGE_THIS_RECORDER_PASSWORD';
const VIEWER_PASSWORD = 'CHANGE_THIS_VIEWER_PASSWORD';
const DATA_FILE = __DIR__ . '/../data/bp.json';
const APP_NAME = 'BP Tracker';
const BP_MIN = 30;
const BP_MAX = 300;
