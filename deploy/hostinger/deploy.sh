#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIR="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
PUBLIC_DIR="${2:-$(dirname "$APP_DIR")/public_html}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer2}"

if [[ ! -f "$APP_DIR/artisan" || ! -f "$APP_DIR/.env" ]]; then
    echo "Aplicação Laravel ou arquivo .env não encontrado em: $APP_DIR" >&2
    exit 1
fi

if [[ ! -d "$PUBLIC_DIR" ]]; then
    echo "Diretório público não encontrado: $PUBLIC_DIR" >&2
    exit 1
fi

cd "$APP_DIR"

"$COMPOSER_BIN" install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

"$PHP_BIN" artisan down || true

restore_application() {
    "$PHP_BIN" artisan up || true
}

trap restore_application EXIT

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan db:seed --force
"$PHP_BIN" artisan optimize
"$PHP_BIN" artisan schedule:interrupt

cp -R "$APP_DIR/public/." "$PUBLIC_DIR/"
cp "$APP_DIR/deploy/hostinger/public-index.php" "$PUBLIC_DIR/index.php"

"$PHP_BIN" artisan up
trap - EXIT

echo "Implantação concluída em: $PUBLIC_DIR"
