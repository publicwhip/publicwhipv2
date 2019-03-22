<?php
declare(strict_types = 1);

namespace PublicWhip\v1migrationUtils;

use PDO;
use Psr\Log\NullLogger;
use PublicWhip\Providers\WikiParserProvider;
use ReflectionClass;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'loader.php';
/**
 * Extracts divisions which increase the code coverage in the wiki parser.
 * phpcs:disable PSR1.Files.SideEffects
 * phpcs:disable Squiz.Functions.GlobalFunction.Found
 * Side effects disabled as we can eat a lot of memory during compilation.
 */

$extractionUtils = new ExtractionUtils();

$logger = new NullLogger();
// we want to keep track of the usage of the wikiparser.
$wikiParser = new WikiParserProvider($logger);
$wikiParserFilename = (new ReflectionClass(WikiParserProvider::class))->getFileName();
$wikiLinesAlreadyCovered = [];
// store our extracted divisions.
$extractedDivisions = [];


xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_WHITELIST, [$wikiParserFilename]);


$extractPdo = $extractionUtils->getPdo();
$extractStatement = $extractPdo->prepare(
    'SELECT division_id, division_date, division_number, house,' .
    'division_name, source_url, motion, debate_url, source_gid, debate_gid ' .
    'FROM pw_division ORDER BY division_id ASC'
);
$getWikiStatement = $extractPdo->prepare(
    'SELECT wiki_id, text_body, edit_date FROM pw_dyn_wiki_motion WHERE ' .
    'division_date=? AND division_number=? AND house=?'
);
$extractStatement->execute();
$migrationRow = $extractStatement->fetch(PDO::FETCH_ASSOC);

// which division ids should we always include?
$alwaysIncludeDivisionIds = [12682, 12376, 12354, 12576, 10818, 10552, 10401, 12682, 12376, 33643];

$uniqueActions = [];
while ($migrationRow) {
    $getWikiStatement->execute([
        $migrationRow['division_date'],
        $migrationRow['division_number'],
        $migrationRow['house']
    ]);

    // give a chance of clearing our cache so we get a nice spread sample.
    if (in_array((int)$migrationRow['division_id'], $alwaysIncludeDivisionIds) || 1 === random_int(0, 1000)) {
        $wikiLinesAlreadyCovered = [];
    }
    $wikiData = $getWikiStatement->fetchAll(PDO::FETCH_ASSOC);

    /**
     * We want to run as much as possible over the code coverage system to pick up all variants we can.
     */
    xdebug_start_code_coverage();
    $output = [];
    // test with no wiki text
    $wikiText = $wikiParser->toWiki($migrationRow['division_name'], $migrationRow['motion'], '');
    $wikiParser->parseMotionTextFromWikiForEdit($wikiText);
    $wikiParser->parseMotionTextFromWiki($wikiText);
    $wikiParser->parseDivisionTitleFromWiki($wikiText);
    $wikiParser->parseActionTextFromWiki($wikiText);
    $wikiParser->parseCommentTextFromWiki($wikiText);
    $wikiParser->parseCommentTextFromWiki($wikiText, true);
    $wikiParser->parseActionTextFromWiki($wikiText);

    $v1Output = $extractionUtils->getWikiTextFromV1('', $migrationRow['division_name'], $migrationRow['motion']);
    $uniqueActions = array_merge($uniqueActions, array_keys($v1Output['actionText']));
    if ($extractionUtils->hasCodeCoverageIncreased($wikiParserFilename, $wikiLinesAlreadyCovered)) {
        $output[] = [
            'divisionName' => $migrationRow['division_name'],
            'motion' => $migrationRow['motion'],
            'wikiText' => '',
            'v1' => $v1Output
        ];
    }
    // loop over all the wiki text
    foreach ($wikiData as $lastWikiEntry) {
        $wikiText = $wikiParser->toWiki(
            $migrationRow['division_name'],
            $migrationRow['motion'],
            $lastWikiEntry['text_body']
        );
        $wikiParser->parseMotionTextFromWikiForEdit($wikiText);
        $wikiParser->parseMotionTextFromWiki($wikiText);
        $wikiParser->parseDivisionTitleFromWiki($wikiText);
        $wikiParser->parseActionTextFromWiki($wikiText);
        $wikiParser->parseCommentTextFromWiki($wikiText);
        $wikiParser->parseCommentTextFromWiki($wikiText, true);
        $wikiParser->parseActionTextFromWiki($wikiText);

        $v1Output = $extractionUtils->getWikiTextFromV1(
            $lastWikiEntry['text_body'],
            $migrationRow['division_name'],
            $migrationRow['motion']
        );
        $uniqueActions = array_map('strtolower', array_merge($uniqueActions, array_keys($v1Output['actionText'])));
        if (!$extractionUtils->hasCodeCoverageIncreased($wikiParserFilename, $wikiLinesAlreadyCovered)) {
            continue;
        }

        $output[] = [
            'divisionName' => $migrationRow['division_name'],
            'motion' => $migrationRow['motion'],
            'wikiText' => $lastWikiEntry['text_body'],
            'v1' => $v1Output
        ];
    }

    xdebug_stop_code_coverage();

    if (count($output) > 0) {
        print 'Saving division ' . $migrationRow['division_id'] . PHP_EOL;
        $extractedDivisions[$migrationRow['division_id']] = $output;
    }

    $migrationRow = $extractStatement->fetch(PDO::FETCH_ASSOC);
}

print 'Action keys: ' . implode(', ', array_unique($uniqueActions)) . PHP_EOL;

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
