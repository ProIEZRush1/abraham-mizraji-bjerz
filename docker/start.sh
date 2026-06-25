#!/usr/bin/env sh
set -e
cd /app
[ -f .env ] || cp .env.production .env
# Ensure a key exists in .env, then EXPORT it so it overrides any empty
# APP_KEY container env var that the platform may inject (which Dotenv won't override).
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force
export APP_KEY="$(grep '^APP_KEY=' .env | head -1 | cut -d '=' -f2-)"
php artisan config:clear >/dev/null 2>&1 || true
exec php artisan serve --host 0.0.0.0 --port 8080
