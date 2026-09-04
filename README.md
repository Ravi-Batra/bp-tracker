# BP Tracker

A small mobile-first blood pressure recorder for one person. A **Recorder** can add and correct two Morning and two Evening readings per date. Every reading includes systolic, diastolic, pulse, date, and editable time. A **Viewer** can only view the readings. There is intentionally no delete function.

Password Protection can be switched ON or OFF by the owner. OFF is the default when `data/settings.json` does not exist. OFF opens the tracker without login and gives Recorder-level add, view, and edit access. ON restores the existing Recorder and Viewer passwords.

## Hostinger private files

The live site keeps `config.php`, `bp.json`, and the automatically created `settings.json` in a folder named `bp-tracker-private` beside `public_html`. Git deployment is restricted to `public_html`, so it cannot overwrite those private files.

`bootstrap.php` loads `bp-tracker-private/config.php` on Hostinger. On the laptop it falls back to the ignored `config/config.php`. The live private config sets `DATA_FILE` to `__DIR__ . '/bp.json'`.

Do **not** upload `tools/`, `tmp/`, ZIP packages, backups, or `config/config.example.php` as part of a deployment.

## First-time password setup

1. Open `config/config.php`.
2. Change the text inside the quotes on `RECORDER_PASSWORD` and `VIEWER_PASSWORD`.
3. Use two different passwords. Do not include a single quote (`'`) in either password.
4. Keep `config/config.php` private: it is ignored by Git and protected by `config/.htaccess`.

This intentionally keeps the passwords easy to change in Hostinger File Manager. It is less secure than password hashes, so do not reuse an email, banking, or Hostinger password.

## Owner settings

The small **Settings** link opens `settings.php`. Enter the separate owner password to switch Password Protection ON or OFF. Owner authorization expires after 15 minutes, or immediately when **Lock owner settings** is pressed.

The owner password is stored only as `OWNER_PASSWORD_HASH` in `config/config.php`. Generate a replacement hash locally with `php tools/password_hash.php`, then replace only the hash text in the live config. Never store or upload the plain owner password.

Important: while Password Protection is OFF, anyone who can reach the website can view, add, and edit the readings. Switch it ON when public access is not intended.

## BP data

The data is stored as JSON at the path set by `DATA_FILE`. Locally it is `data/bp.json`; live it is `bp-tracker-private/bp.json` outside `public_html`. The ON/OFF state is stored as `settings.json` beside `bp.json`. Ordinary code deployments do not touch this private folder. The app locks both files while writing so two near-simultaneous saves do not corrupt them.

## Safe updates after the app is live

1. Back up the live JSON data file in Hostinger first.
2. Upload only the exact changed code file(s).
3. Do not replace `bp.json` or copy the full local project folder over the live site.
4. Sign in as Recorder, confirm the current readings still show, then test the changed feature.

For the Password Protection update, preserve the live `config/config.php` and manually add `OWNER_PASSWORD_HASH`. Upload only `index.php`, `lib/auth.php`, and the new `settings.php`. Do not upload or replace `data/bp.json`. Preserve `data/settings.json` in later deployments.

## Hosting

Use an unpredictable subdomain, for example `x7k29p.yourdomain.com`, but treat it only as an extra privacy layer. The passwords and server-side role checks are the real protection.

Staging deployments are delivered automatically from the GitHub `staging` branch.

## Later MySQL migration

The page and validation do not directly edit JSON. The storage work is isolated in `lib/storage.php` and `lib/bp.php`, and access decisions are centralized in `lib/auth.php`. A later multi-user version can move storage to MySQL, add user accounts, and associate every reading with a `user_id`. This release does not add accounts or separate user data.

## Password Protection update files and tests

Runtime files changed or created: `config/config.php`, `lib/auth.php`, `index.php`, and new `settings.php`. Supporting project files updated: `config/config.example.php`, `.gitignore`, `README.md`, and `PROJECT.md`.

Tested on 03-09-2026: PHP syntax, default OFF access, owner-password rejection and acceptance, ON/OFF persistence, return of the protected login screen, and protected Doctor Report access. Existing BP readings were not changed by these tests.
