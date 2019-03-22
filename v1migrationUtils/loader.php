<?php
declare(strict_types =1);

/**
 * Load the original publicwhip code.
 */
$originalPublicWhipCodeBase = dirname(__DIR__) .
    DIRECTORY_SEPARATOR . 'originalPublicWhipCode' . DIRECTORY_SEPARATOR;

require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'common.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'config.php';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'db.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'decodeids.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'tablepeop.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'tablemake.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'tableoth.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'account/user.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'database.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'divisionvote.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'dream.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'distances.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'parliaments.inc';

// override pw1's error handler.
set_error_handler(static function (int $errno, string $errstr, ?string $errfile, ?int $errline): void {
    print 'Died with error level ' . $errno . ': ' . $errstr . ' in ' .
        ($errfile ?? '[unknown]') . ' at ' . ($errline ?? 0);
    die;
});

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
