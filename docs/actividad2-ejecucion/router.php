<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = dirname(__DIR__, 2);
if (preg_match('#^/(build/assets/|images/|favicon\\.ico)#', $path)) {
    $file = realpath($root.'/public/'.$path);
    $public = realpath($root.'/public');
    if ($file && is_file($file) && str_starts_with($file, $public.DIRECTORY_SEPARATOR)) return false;
}
$app = require __DIR__.'/bootstrap.php';
$app->handleRequest(Illuminate\Http\Request::capture());
