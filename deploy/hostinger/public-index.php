<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Aplicação fora do diretório público
|--------------------------------------------------------------------------
|
| Na Hostinger Business, coloque o repositório na pasta "laravel_app",
| irmã de "public_html". Somente este arquivo, o .htaccess e os demais
| arquivos de public/ devem ficar acessíveis pela web.
|
*/
$appPath = dirname(__DIR__).'/laravel_app';

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
