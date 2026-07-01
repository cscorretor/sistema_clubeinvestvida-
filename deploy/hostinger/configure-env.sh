#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PHP_BIN="${PHP_BIN:-/opt/alt/php82/usr/bin/php}"
ENV_FILE="$APP_DIR/.env"
ENV_TEMPLATE="$APP_DIR/deploy/hostinger/env.production.example"

if [[ ! -x "$PHP_BIN" ]]; then
    echo "PHP 8.2 não encontrado em: $PHP_BIN" >&2
    exit 1
fi

if [[ ! -f "$ENV_TEMPLATE" ]]; then
    echo "Modelo de ambiente não encontrado: $ENV_TEMPLATE" >&2
    exit 1
fi

read -rp "E-mail do administrador: " ADMIN_EMAIL
read -rsp "Senha real do banco de homologação: " DB_PASSWORD
echo
read -rsp "Senha temporária do administrador (mínimo 12 caracteres): " ADMIN_PASSWORD
echo
read -rsp "Repita a senha temporária do administrador: " ADMIN_PASSWORD_CONFIRMATION
echo

if [[ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRMATION" ]]; then
    echo "As senhas do administrador não conferem." >&2
    exit 1
fi

export ADMIN_EMAIL DB_PASSWORD ADMIN_PASSWORD
trap 'unset ADMIN_EMAIL DB_PASSWORD ADMIN_PASSWORD ADMIN_PASSWORD_CONFIRMATION' EXIT

cd "$APP_DIR"

"$PHP_BIN" <<'PHP'
<?php

$file = '.env';
$template = 'deploy/hostinger/env.production.example';
$adminEmail = getenv('ADMIN_EMAIL');
$dbPassword = getenv('DB_PASSWORD');
$adminPassword = getenv('ADMIN_PASSWORD');

if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "E-mail inválido.\n");
    exit(1);
}

if ($dbPassword === '') {
    fwrite(STDERR, "A senha do banco não pode ficar vazia.\n");
    exit(1);
}

if (strlen($adminPassword) < 12) {
    fwrite(STDERR, "A senha do administrador precisa ter pelo menos 12 caracteres.\n");
    exit(1);
}

$existingLines = file_exists($file)
    ? file($file, FILE_IGNORE_NEW_LINES)
    : [];

if ($existingLines === false) {
    fwrite(STDERR, "Não foi possível ler o arquivo .env existente.\n");
    exit(1);
}

$appKey = null;

foreach ($existingLines as $line) {
    if (str_starts_with($line, 'APP_KEY=')) {
        $currentKey = trim(substr($line, strlen('APP_KEY=')), " \t\n\r\0\x0B\"'");

        if ($currentKey !== '') {
            $appKey = $currentKey;
        }

        break;
    }
}

$appKey ??= 'base64:'.base64_encode(random_bytes(32));

$lines = file($template, FILE_IGNORE_NEW_LINES);

if ($lines === false) {
    fwrite(STDERR, "Não foi possível ler o modelo de ambiente.\n");
    exit(1);
}

$values = [
    'APP_NAME' => 'Sistema Clube Investvida',
    'APP_ENV' => 'production',
    'APP_KEY' => $appKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'https://sistema-hml.cscorretor.com.br',
    'APP_TIMEZONE' => 'America/Sao_Paulo',
    'APP_LOCALE' => 'pt_BR',
    'APP_FALLBACK_LOCALE' => 'pt_BR',
    'APP_FAKER_LOCALE' => 'pt_BR',
    'LOG_LEVEL' => 'warning',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'u118920581_hml',
    'DB_USERNAME' => 'u118920581_hml',
    'DB_PASSWORD' => $dbPassword,
    'SESSION_DRIVER' => 'file',
    'SESSION_ENCRYPT' => 'true',
    'SESSION_SECURE_COOKIE' => 'true',
    'SESSION_HTTP_ONLY' => 'true',
    'SESSION_SAME_SITE' => 'lax',
    'QUEUE_CONNECTION' => 'sync',
    'CACHE_STORE' => 'file',
    'MAIL_MAILER' => 'log',
    'MAIL_FROM_ADDRESS' => 'sistema@cscorretor.com.br',
    'SEED_ADMIN_NAME' => 'Administrador',
    'SEED_ADMIN_EMAIL' => $adminEmail,
    'SEED_ADMIN_PASSWORD' => $adminPassword,
];

$quote = static fn (string $value): string => json_encode(
    $value,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
);

$seen = [];

foreach ($lines as $index => $line) {
    if (preg_match('/^([A-Z][A-Z0-9_]*)=/', $line, $match) !== 1) {
        continue;
    }

    $key = $match[1];

    if (! array_key_exists($key, $values)) {
        continue;
    }

    $lines[$index] = $key.'='.$quote($values[$key]);
    $seen[$key] = true;
}

foreach ($values as $key => $value) {
    if (! isset($seen[$key])) {
        $lines[] = $key.'='.$quote($value);
    }
}

$written = file_put_contents($file, implode(PHP_EOL, $lines).PHP_EOL);

if ($written === false) {
    fwrite(STDERR, "Não foi possível salvar o arquivo .env.\n");
    exit(1);
}

echo "Configuração gravada sem exibir senhas.\n";
PHP

chmod 600 "$ENV_FILE"

echo "Ambiente de homologação configurado com sucesso."
echo "URL: https://sistema-hml.cscorretor.com.br"
echo "Banco: u118920581_hml"
