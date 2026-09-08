#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

export COMPOSE_FILE=compose.prod.yaml:compose.override.yaml

echo "==> Pulling latest code"
git fetch --prune origin
git reset --hard origin/main

echo "==> Building image"
docker compose build app

echo "==> Restarting services"
docker compose up -d

echo "==> Waiting for app container"
for i in $(seq 1 30); do
    if docker compose ps app --format '{{.State}}' | grep -q running; then
        break
    fi
    sleep 2
done

echo "==> Running migrations"
docker compose exec -T app php artisan migrate --force

echo "==> Rebuilding caches"
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

echo "==> Restarting workers"
docker compose exec -T app php artisan queue:restart

echo "==> Reloading nginx"
docker compose restart nginx

echo "==> Pruning old images"
docker image prune -f

echo "==> Deploy finished"
