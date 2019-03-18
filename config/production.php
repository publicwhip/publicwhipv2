<?php
declare(strict_types=1);

return [
    'settings.db' => [
        'driver' => 'mysql',
        'host' => 'mariadb',
        'database' => 'publicwhip-db',
        'username' => 'publicwhip-user',
        'password' => 'password',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix' => ''
    ],
    'settings.logger' => [
        'path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log'
    ],
    'settings.mail' => [
        'transport' => 'sendmail',
        'host' => 'mailhog',
        'port' => 1025,
        'username' => '',
        'password' => ''
    ],
    'settings.debug' => false,
    'settings.templates.cache' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache'
];
