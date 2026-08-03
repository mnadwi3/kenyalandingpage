#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Ensuring writable directories"
mkdir -p storage/logs storage/cache uploads public/uploads/doctors public/uploads/treatments
chmod -R u+rwX storage uploads public/uploads

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "==> Created .env from .env.example"
fi

echo "==> Local setup files are ready"
echo "Start server with:"
echo "  php -S 127.0.0.1:8080 -t public public/router.php"
