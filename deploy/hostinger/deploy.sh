#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIR="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
PUBLIC_DIR="${2:-$(dirname "$APP_DIR")/public_html}"
COMPOSER_BIN="${COMPOSER_BIN:-composer2}"

if [[ -z "${PHP_BIN:-}" ]]; then
    if [[ -x /opt/alt/php82/usr/bin/php ]]; then
        PHP_BIN=/opt/alt/php82/usr/bin/php
    else
        PHP_BIN=php
    fi
fi

if [[ ! -f "$APP_DIR/artisan" || ! -f "$APP_DIR/.env" ]]; then
    echo "Aplicação Laravel ou arquivo .env não encontrado em: $APP_DIR" >&2
    exit 1
fi

if [[ ! -d "$PUBLIC_DIR" ]]; then
    echo "Diretório público não encontrado: $PUBLIC_DIR" >&2
    exit 1
fi

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
    echo "Executável PHP não encontrado: $PHP_BIN" >&2
    exit 1
fi

PHP_VERSION="$("$PHP_BIN" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"

if [[ "$PHP_VERSION" != "8.2" ]]; then
    echo "PHP 8.2 é obrigatório; encontrado: $PHP_VERSION" >&2
    exit 1
fi

COMPOSER_PATH="$(command -v "$COMPOSER_BIN" || true)"

if [[ -z "$COMPOSER_PATH" ]]; then
    echo "Composer 2 não encontrado: $COMPOSER_BIN" >&2
    exit 1
fi

cd "$APP_DIR"

"$PHP_BIN" "$COMPOSER_PATH" install \
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
