# sl-fit-assessment

Squad Limpul Community Fit Assessment is a Laravel-based, non-clinical assessment platform for the Squad Limpul Roblox community.

The application helps admins review community fit, risk signals, SL Profile Code, notes, interviews, and final decisions. Participant-facing pages only show the assessment flow and completion status; scoring details remain admin-only.

## Stack

- Laravel 13
- Blade
- Tailwind CSS via Vite
- SQLite for local MVP development
- UUID primary keys for main entities

## Local Setup

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve
```

Default local admin seed:

```text
admin@squadlimpul.local / password
```

## Verification

```bash
php artisan test
npm run build
```

## Project Notes

Detailed handoff notes are kept in `PROJECT_HANDOFF_SUMMARY.md`.
