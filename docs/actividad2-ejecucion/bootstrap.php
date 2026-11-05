<?php
// Bootstrap exclusivo de pruebas: no lee ni modifica la base definida en .env.
$root = dirname(__DIR__, 2);
$qa = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sistema-autenticidad-actividad2-20261025-entrega';
foreach (['', '/framework/views', '/framework/sessions', '/framework/cache/data', '/logs', '/app/public/documentos', '/app/private', '/backups'] as $dir) {
    if (!is_dir($qa.$dir)) mkdir($qa.$dir, 0777, true);
}
$env = [
    'APP_ENV'=>'testing', 'APP_DEBUG'=>'false', 'APP_NAME'=>'Sistema de autenticidad',
    'APP_KEY'=>'base64:'.base64_encode(str_repeat('A', 32)), 'APP_URL'=>'http://127.0.0.1:8775',
    'DB_CONNECTION'=>'sqlite', 'DB_DATABASE'=>$qa.'/qa.sqlite', 'DB_URL'=>'',
    'SESSION_DRIVER'=>'file', 'SESSION_COOKIE'=>'actividad2_qa', 'SESSION_SECURE_COOKIE'=>'false',
    'CACHE_STORE'=>'file', 'MAIL_MAILER'=>'array', 'QUEUE_CONNECTION'=>'sync',
    'APP_CONFIG_CACHE'=>$qa.'/config-unused.php', 'APP_ROUTES_CACHE'=>$qa.'/routes-unused.php',
    'VIEW_COMPILED_PATH'=>$qa.'/framework/views',
];
foreach ($env as $key=>$value) { putenv("$key=$value"); $_ENV[$key]=$value; $_SERVER[$key]=$value; }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->useStoragePath($qa);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['filesystems.backups_path'=>$qa.'/backups', 'app.name'=>'Sistema de autenticidad']);
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $qa.'/qa.sqlite') {
    throw new RuntimeException('La conexión no apunta a SQLite temporal de pruebas.');
}
return $app;
