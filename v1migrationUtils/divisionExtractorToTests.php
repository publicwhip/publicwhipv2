<?php /** @noinspection PhpUndefinedConstantInspection */
/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection MessDetectorValidationInspection */
declare(strict_types=1);

/**
 * Extracts divisions which increase the code coverage in the wiki parser.
 * phpcs:disable PSR1.Files.SideEffects
 */

use Psr\Log\NullLogger;
use PublicWhip\Providers\WikiParserProvider;

ini_set('memory_limit', '512M');
/**
 * Requires:
 * -  /usr/local/bin/docker-php-ext-install mysqli
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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

$logger = new NullLogger();
// we want to keep track of the usage of the wikiparser.
$wikiParser = new WikiParserProvider($logger);
$wikiParserFilename = (new ReflectionClass(WikiParserProvider::class))->getFileName();
$wikiLinesAlreadyCovered = [];
// store our extracted divisions.
$extractedDivisions = [];
// which division ids should we always include?
$alwaysIncludeDivisionIds = [12682, 12376, 12354, 12576, 10818];

// override pw1's error handler.
set_error_handler(function (int $errno, string $errstr, ?string $errfile, ?int $errline): void {
    print 'Died with error level ' . $errno . ': ' . $errstr . ' in ' .
        ($errfile ?? '[unknown]') . ' at ' . ($errline ?? 0);
    die();
});
xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_WHITELIST, [$wikiParserFilename]);

try {
    $extractPdo = new PDO(
        'mysql:dbname=' . $GLOBALS['pw_database'] . ';host=' . $GLOBALS['pw_host'],
        $GLOBALS['pw_user'],
        $GLOBALS['pw_password'],
        [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
    );
    $extractPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $exception) {
    die('Unable to access database:' . $exception->getMessage());
}

$extractStatement = $extractPdo->prepare(
    'SELECT division_id, division_date, division_number, house,' .
    'division_name, source_url, motion, debate_url, source_gid, debate_gid ' .
    'FROM pw_division ORDER BY division_id ASC'
);
$getWikiStatement = $extractPdo->prepare(
    'SELECT wiki_id, text_body, edit_date FROM pw_dyn_wiki_motion WHERE ' .
    'division_date=? AND division_number=? AND house=? ORDER BY wiki_id DESC LIMIT 1'
);
$extractStatement->execute();
$migrationRow = $extractStatement->fetch(PDO::FETCH_ASSOC);
while ($migrationRow) {
    print PHP_EOL . 'Processing division ' . $migrationRow['division_id'];
    $getWikiStatement->execute([
        $migrationRow['division_date'],
        $migrationRow['division_number'],
        $migrationRow['house']
    ]);
    $wikiData = $getWikiStatement->fetchAll(PDO::FETCH_ASSOC);

    xdebug_start_code_coverage();

    $lastWikiEntry = last($wikiData);
    $wikiParser->parseMotionText($lastWikiEntry['text_body'] ?? '', $migrationRow['motion']);
    $wikiParser->parseDivisionTitle($lastWikiEntry['text_body'] ?? '', $migrationRow['division_name']);

    $codeCoverage = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();

    $wikiLinesCovered = array_keys($codeCoverage[$wikiParserFilename]);
    $newLines = array_diff($wikiLinesCovered, $wikiLinesAlreadyCovered);
    // have we got a division we want to include?
    if (count($newLines) > 0 || in_array((int)$migrationRow['division_id'], $alwaysIncludeDivisionIds, true)) {
        $wikiLinesAlreadyCovered = array_unique(array_merge($wikiLinesAlreadyCovered, $wikiLinesCovered));
        $extractedDivisions[$migrationRow['division_id']] = [
            'division' => $migrationRow,
            'wiki' => $wikiData,
            'v1' => getResultsFromV1($migrationRow)
        ];
    }
    $migrationRow = $extractStatement->fetch(PDO::FETCH_ASSOC);
}
file_put_contents(
    __DIR__ .
    DIRECTORY_SEPARATOR . '..' .
    DIRECTORY_SEPARATOR . 'tests' .
    DIRECTORY_SEPARATOR . 'Unit' .
    DIRECTORY_SEPARATOR . 'Providers' .
    DIRECTORY_SEPARATOR . 'WikiParserProvider' .
    DIRECTORY_SEPARATOR . 'mockDivisions.json',
    json_encode($extractedDivisions, JSON_PRETTY_PRINT)
);

/**
 * Pass it through version 1 to extract how it would handle it.
 *
 * @param array<string,string> $migrationRow Data from the database.
 *
 * @return array<string,string> Extracted Data.
 */
function getResultsFromV1(array $migrationRow): array
{
    // setup the get variables as expected in v1.
    $_GET['date'] = $migrationRow['division_date'];
    $_GET['number'] = $migrationRow['division_number'];
    $_GET['house'] = $migrationRow['house'];
    // let v1 handle it as it does in divisions.php
    $divattr = get_division_attr_decode();
    if ('none' === $divattr) {
        throw new RuntimeException('Failed when fetching division ' . $migrationRow['division_id']);
    }
    $motionData = get_wiki_current_value(
        'motion',
        [$divattr['division_date'], $divattr['division_number'], $divattr['house']]
    );
    $name = extract_title_from_wiki_text($motionData['text_body']);
    $debateGid = $divattr['debate_gid'];


    $ldalink = null;

    if (('lords' === $divattr['house']) && ($divattr['division_date'] >= '2009-01-21')) {
        $ldasess = '2008_09';
        $ldadate = str_replace('-', '', $divattr['division_date']);
        $ldanum = '/number/' . $divattr['division_number'];
        $ldalink = "http://services.parliament.uk/LordsDivisionsAnalysis/session/$ldasess/division/$ldadate$ldanum";
    }

    $theyWorkForYouLink = null;
    if ('' !== $debateGid) {
        if ('lords' === $divattr['house']) {
            $debateGid = 'lords/?id=' . str_replace('uk.org.publicwhip/lords/', '', $debateGid);
        } elseif ('scotland' === $divattr['house']) {
            $debateGid = 'sp/?id=' . str_replace('uk.org.publicwhip/spor/', '', $debateGid);
        } else {
            $debateGid = 'debates/?id=' . str_replace('uk.org.publicwhip/debate/', '', $debateGid);
        }
        $theyWorkForYouLink = 'http://www.theyworkforyou.com/' . $debateGid;
    }

    // hansard
    $historicalHansard = null;
    if ($divattr['division_date'] <= '2005-03-17') {
        $millbankurl = $divattr['house']
            . '/' .
            substr(
                $divattr['division_date'],
                0,
                4
            )
            . '/' .
            substr($divattr['division_date'], 5, 2) . '/' .
            substr(
                $divattr['division_date'],
                8,
                2
            ) .
            '/division_' . $divattr['division_number'];
        $historicalHansard = 'http://hansard.millbanksystems.com/' . $millbankurl;
    }

    $output = [
        'title' => $name,
        'description' => extract_motion_text_from_wiki_text($motionData['text_body']),
        'actionText' => extract_action_text_from_wiki_text($motionData['text_body'])
    ];
    if ($ldalink) {
        $output['ldaLink'] = $ldalink;
    }
    if ($theyWorkForYouLink) {
        $output['theyWorkForYouLink'] = $theyWorkForYouLink;
    }
    if ($historicalHansard) {
        $output['historicalHansard'] = $historicalHansard;
    }
    return $output;
}
