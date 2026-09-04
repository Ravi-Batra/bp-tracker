# BP Tracker — Project Guide

## Current version

Version 1 uses PHP and a JSON data file. It is built for Hostinger shared hosting and is primarily designed for a phone.

## What the app does

- Recorder: add or edit Morning and Evening BP readings.
- Viewer: see the same readings but cannot add or edit.
- Two Morning and two Evening readings maximum per date.
- Required pulse and an editable time for every reading.
- Latest 30-day window, with newest dates first.
- No delete function.
- Owner-controlled Password Protection ON/OFF; missing settings default to OFF.
- OFF gives direct Recorder-level add/view/edit access. ON uses the existing Recorder/Viewer roles.
- Separate owner-password protection for changing the setting.

## File map

| Area | File/folder | Purpose |
|---|---|---|
| Page | `index.php` | Login screen and main BP table |
| Styling | `assets/style.css` | All colours, spacing, and mobile design |
| Browser interaction | `assets/app.js` | Opens entry/edit forms |
| Login security | `lib/auth.php` | Sessions, roles, CSRF protection |
| Owner settings | `settings.php` | Owner authentication and Password Protection control |
| BP rules | `lib/validation.php`, `lib/bp.php` | Validation, duplicates, editing, table window |
| Storage | `lib/storage.php` | Safe JSON reading/writing with file locking |
| Configuration | Local `config/config.php`; live `bp-tracker-private/config.php` | Recorder/Viewer passwords, owner-password hash, and data-file location |
| Live entries | Live `bp-tracker-private/bp.json` outside `public_html` | The actual BP readings — isolated from Git deployment |
| Access state | Live `bp-tracker-private/settings.json` outside `public_html` | Automatically created ON/OFF state — isolated from Git deployment |

## Before each Hostinger update

- [ ] Back up the live BP JSON file.
- [ ] Know exactly which code file changed.
- [ ] Upload only that changed code file.
- [ ] Do not upload a local `bp.json` over the live one.
- [ ] Do not overwrite the live `config/config.php` or `settings.json`.
- [ ] Check existing readings after upload.

## Important rule

The code may be updated often. The live BP data is separate and must be preserved.

## Changing passwords later

In Hostinger File Manager, open `config/config.php` and change only the text inside the quotes after `RECORDER_PASSWORD` or `VIEWER_PASSWORD`. Do not change the surrounding PHP code.

## Password Protection owner control

Open **Settings**, enter the separate owner password, select ON or OFF, and save. OFF gives anyone with the site URL Recorder-level add/view/edit access. ON restores the Recorder/Viewer login. Owner authorization lasts 15 minutes and can be ended with **Lock owner settings**.

The owner password is stored as `OWNER_PASSWORD_HASH` in the protected config. Generate a new hash locally with `php tools/password_hash.php`; never put the plain owner password in a project file.

## Deployment for this update

1. Back up the live `data/bp.json` and `config/config.php`.
2. Manually add `OWNER_PASSWORD_HASH` to the existing live config; do not replace that config file.
3. Upload `index.php`, `lib/auth.php`, and new `settings.php`.
4. Open the tracker and confirm the expected OFF state, then test the owner Settings password and both modes.
5. Preserve the automatically created `data/settings.json` during later updates.

## Testing completed 03-09-2026

- PHP syntax passed for all affected runtime PHP files.
- OFF mode opened without login and retained add/view/edit controls.
- Wrong owner password was rejected; the correct owner password opened Settings.
- ON mode restored the protected login screen and blocked direct Doctor Report access.
- Switching back OFF worked without changing saved BP readings.

## Future multi-user upgrade

Do not add multiple users to the JSON version. A future upgrade should use MySQL accounts, secure password hashes, and a `user_id` on every reading so each user sees only their own data. The centralized access logic in `lib/auth.php` is intended to support that later migration.
