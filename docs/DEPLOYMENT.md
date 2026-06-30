# Deployment and Operations

## Required production settings

- Use PHP 8.3+, MySQL 8+, Node 20+ for asset compilation, and HTTPS.
- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_TIMEZONE=Asia/Manila`.
- Set `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, and a production domain.
- Use `QUEUE_CONNECTION=database` or Redis and supervise `php artisan queue:work --tries=3`.
- Use daily or centralized logs at `warning` or `info` level.
- Store `.env` and mail/database credentials outside source control.

## Release

1. Back up the database and verify the backup can be read.
2. Put the application in maintenance mode.
3. Install locked PHP and Node dependencies and run `npm run build`.
4. Run `php artisan migrate --force`.
5. Run `php artisan optimize`, restart PHP workers, and restart queue workers.
6. Bring the application up and check `/up`, login, reservation creation, and queue processing.

## Rollback and recovery

- Keep the prior release and asset bundle available.
- Prefer forward-fix migrations. Before destructive migration rollback, restore the pre-release database backup.
- Monitor `failed_jobs` and application logs; retry only idempotent jobs.
- Test database restore at least quarterly and retain backups according to the office records policy.

## Staging acceptance

- Verify SMTP delivery, reset/setup links, queued notifications, and failed-job handling.
- Inspect both PDF templates on A4 and long-bond output.
- Verify staff/admin permissions, token expiry/deactivation, exports, and mobile layouts.
