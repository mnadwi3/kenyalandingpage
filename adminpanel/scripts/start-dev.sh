#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

# Ensure MariaDB is up (Ubuntu service name)
if command -v service >/dev/null 2>&1; then
  sudo service mariadb start >/dev/null 2>&1 || true
fi

mkdir -p storage/logs storage/cache uploads public/uploads/doctors public/uploads/treatments public/uploads/hospitals
chmod -R u+rwX storage uploads public/uploads 2>/dev/null || true

HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8080}"

echo "VaidTrack Admin → http://${HOST}:${PORT}/login"
echo "Document root: ${ROOT}/public"
exec php -S "${HOST}:${PORT}" -t public public/router.php
