#!/usr/bin/env sh
# Production entrypoint. Deliberately NO `set -e`: a migrate/seed hiccup or a
# slow/unready Postgres must never crash the container or block serving.
cd /app || exit 1

# 1. Ensure a .env exists.
[ -f .env ] || cp .env.production .env

# 2. Ensure a valid APP_KEY and export it so request handlers read the baked
#    key (overrides any empty injected env var).
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force || true
fi
APP_KEY="$(grep '^APP_KEY=' .env | head -1 | cut -d '=' -f2-)"
export APP_KEY

# 3. Ensure the SQLite database file and its directory exist (default path).
DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"
export DB_DATABASE
DB_DIR="$(dirname "$DB_DATABASE")"
mkdir -p "$DB_DIR" 2>/dev/null || true
[ -f "$DB_DATABASE" ] || touch "$DB_DATABASE" 2>/dev/null || true

# Make runtime dirs writable (best effort).
mkdir -p storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache "$DB_DIR" 2>/dev/null || true

# 4. Migrate + seed in the BACKGROUND, best-effort with a retry loop, so a
#    slow/unready database never blocks or crashes serving.
(
    i=1
    while [ "$i" -le 40 ]; do
        if php artisan migrate --force 2>&1; then
            php artisan db:seed --force 2>&1 || true
            echo "[entrypoint] migrate/seed completed on attempt $i"
            break
        fi
        echo "[entrypoint] migrate attempt $i failed; retrying in 3s..."
        i=$((i + 1))
        sleep 3
    done
) &

# 5. Cache config (uses the exported APP_KEY). Best effort.
php artisan config:cache || true

# 6. Serve under an auto-respawn loop: if a request ever crashes the dev server, it restarts
#    immediately, so the app is never permanently down (no `set -e`, so the loop always continues).
while true; do
    php artisan serve --host 0.0.0.0 --port 8080
    echo "[entrypoint] server exited (code $?); restarting in 1s..."
    sleep 1
done
