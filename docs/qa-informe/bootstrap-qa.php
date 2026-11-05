<?php
// Entorno exclusivo del informe. Nunca conecta a la base configurada en .env.
$root = dirname(__DIR__, 2);
$qa = sys_get_temp_dir().'/autenticidad-informe-20261016';
foreach (['', '/framework/views','/framework/sessions','/framework/cache/data','/logs','/app/public/documentos','/app/private','/backups'] as $dir) {
    if (!is_dir($qa.$dir)) mkdir($qa.$dir, 0777, true);
}
$env = [
 'APP_ENV'=>'testing', 'APP_DEBUG'=>'false', 'APP_NAME'=>'Sistema de autenticidad',
 'APP_KEY'=>'base64:'.base64_encode(str_repeat('Q',32)), 'APP_URL'=>'http://127.0.0.1:8765',
 'DB_CONNECTION'=>'sqlite', 'DB_DATABASE'=>$qa.'/qa.sqlite', 'DB_URL'=>'',
 'SESSION_DRIVER'=>'file', 'SESSION_COOKIE'=>'autenticidad_qa', 'SESSION_SECURE_COOKIE'=>'false',
 'CACHE_STORE'=>'file', 'MAIL_MAILER'=>'array', 'QUEUE_CONNECTION'=>'sync',
 'APP_CONFIG_CACHE'=>$qa.'/config-unused.php', 'APP_ROUTES_CACHE'=>$qa.'/routes-unused.php',
 'VIEW_COMPILED_PATH'=>$qa.'/framework/views',
];
foreach ($env as $k=>$v) { putenv("$k=$v"); $_ENV[$k]=$v; $_SERVER[$k]=$v; }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->useStoragePath($qa);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['filesystems.backups_path'=>$qa.'/backups','app.name'=>'Sistema de autenticidad']);
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $qa.'/qa.sqlite') {
 throw new RuntimeException('Entorno de prueba incorrecto');
}
return $app;
