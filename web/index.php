<?php
declare(strict_types=1);

use PublicWhip\Web;

if (PHP_SAPI === 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}
require dirname(__DIR__) . '/vendor/autoload.php';

$environment = getenv('PUBLICWHIP_ENVIRONMENT') ?: 'production';
try {
    (new Web($environment))->run();
} catch (Throwable $exception) {
    print 'There was an unrecoverable error of an unknown type. Aborting.';
}