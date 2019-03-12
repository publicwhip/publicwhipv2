<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(
    function ($severity, $message, $file, $line) {
        if (error_reporting() & $severity) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    }
);

return [
    'settings.db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'publicwhip-db',
        'username' => 'publicwhip-user',
        'password' => 'password',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix' => ''
    ],
    'settings.logger' => [
        'name' => 'publicwhip',
        'path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log'
    ],
    'settings.mail' => [
        'transport' => 'null'
    ],
    'settings.debug' => true,
    'settings.templates.cache' => false,

];
