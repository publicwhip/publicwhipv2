<?php
declare(strict_types = 1);

namespace PublicWhip\v1migrationUtils;

/**
 * Compares the wiki output of two divisions between the old and new PublicWhip code.
 */

use Psr\Log\NullLogger;
use PublicWhip\Providers\WikiParserProvider;
use function dirname;
use function ord;
use function strlen;

/**
 * phpcs:disable PSR1.Files.SideEffects
 * phpcs:disable Squiz.Functions.GlobalFunction.Found
 */
$originalPublicWhipCodeBase = dirname(__DIR__) .
    DIRECTORY_SEPARATOR . 'originalPublicWhipCode' . DIRECTORY_SEPARATOR;
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'wiki.inc';
require_once $originalPublicWhipCodeBase . 'website' . DIRECTORY_SEPARATOR . 'pretty.inc';

$divisionId = 10818;

$logger = new NullLogger();
$parser = new WikiParserProvider($logger);

$json = json_decode(
    file_get_contents(
        dirname(__DIR__) .
        DIRECTORY_SEPARATOR . 'tests' .
        DIRECTORY_SEPARATOR . 'Unit' .
        DIRECTORY_SEPARATOR . 'Providers' .
        DIRECTORY_SEPARATOR . 'WikiParserProvider' .
        DIRECTORY_SEPARATOR . 'mockDivisions.json'
    ),
    true
);
$jsonField = $json[$divisionId];
$wikiLast = last($jsonField['wiki']);
$wikiTextBody = $wikiLast['text_body'] ?? '';
$default = $jsonField['division']['motion'];
$divisionName = $jsonField['division']['division_name'];

$theirMotion = $wikiTextBody;

// emulate get_motion_default_values
if ('' === $theirMotion) {
    $theirMotion = $default;
    $theirMotion = str_replace([' class=""', ' pwmotiontext="yes"'], '', $theirMotion);
}

$theirMotion = add_motion_missing_wrappers($theirMotion, $divisionName);

$theirs = extract_motion_text_from_wiki_text_for_edit($theirMotion);
$ours = $parser->parseMotionTextForEdit($wikiTextBody, $default);

if (checkSameness('Edit', $theirs, $ours)) {
    $theirs = extract_motion_text_from_wiki_text($theirMotion);
    $ours = $parser->parseMotionText($wikiTextBody, $default);
    checkSameness('Standard', $theirs, $ours);
}

print 'COMPLETED';

/**
 * Are two strings the same - if they differ - where do they differ?
 *
 * @param string $reasonForCheck What are we checking.
 * @param string $theirs The original text.
 * @param string $ours The output text.
 * @return bool Are they the same?
 */
function checkSameness(string $reasonForCheck, string $theirs, string $ours): bool
{
    if ($theirs === $ours) {
        print $reasonForCheck . ': PASSED';

        return true;
    }

    print $reasonForCheck . ': FAILED' . PHP_EOL;
    $len = strlen($theirs);
    $ourLen = strlen(trim($ours));

    if ($len !== $ourLen) {
        print 'Difference in length: expected ' . $len . ' got ' . $ourLen . PHP_EOL;
    }

    $max = max($len, $ourLen);

    for ($position = 0; $position < $max; $position++) {
        if ($position > $ourLen) {
            print 'Exceeded length of ours';

            break;
        }

        if ($position > $len) {
            print 'Exceeded length of v1';

            break;
        }

        if ($theirs[$position] === $ours[$position]) {
            print $theirs[$position];

            continue;
        }

        print PHP_EOL;
        print '[expected character: ' . $theirs[$position] . ' (' . ord($theirs[$position]) . '):';
        print 'got: ' . $ours[$position] . ' (' . ord($ours[$position]) . ')';
        print PHP_EOL;
        print '------------------- Original -------------' . PHP_EOL;
        print $theirs . PHP_EOL;
        print '------------------- Ours -----------' . PHP_EOL;
        print $ours . PHP_EOL;

        break;
    }

    return false;
}
