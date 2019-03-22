<?php
declare(strict_types = 1);

namespace PublicWhip\v1migrationUtils;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Requires:
 * -  /usr/local/bin/docker-php-ext-install mysqli
 */
class ExtractionUtils
{
    public function getPdo(): PDO
    {
        try {
            $extractPdo = new PDO(
                'mysql:dbname=' . $GLOBALS['pw_database'] . ';host=' . $GLOBALS['pw_host'],
                $GLOBALS['pw_user'],
                $GLOBALS['pw_password'],
                [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
            );
            $extractPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $exception) {
            die('Extractor unable to access database:' . $exception->getMessage() . ':' . json_encode($GLOBALS));
        }

        return $extractPdo;
    }

    /**
     * @param array<int> $ourCurrentLines
     * @return bool
     */
    public function hasCodeCoverageIncreased(string $fileName, array &$ourCurrentLines): bool
    {
        $codeCoverage = xdebug_get_code_coverage();
        if (!isset($codeCoverage[$fileName])) {
            print 'Did not find ' . $fileName . ' in : ' . implode(', ', array_keys($codeCoverage)) . PHP_EOL;
            die;
        }
        $wikiLinesCovered = array_keys($codeCoverage[$fileName]);
        $newLines = array_diff($wikiLinesCovered, $ourCurrentLines);
        if (count($newLines) > 0) {
            $ourCurrentLines = array_unique(array_merge($ourCurrentLines, $wikiLinesCovered));

            return true;
        }

        return false;
    }

    /**
     * @param string $wikiText The wiki text.
     * @param string $divisionTitle The division title.
     * @param string $divisionText The division text.
     * @return array<string,string>
     */
    public function getWikiTextFromV1(string $wikiText, string $divisionTitle, string $divisionText): array
    {
        $ourText = $wikiText;
        if ('' === $ourText) {
            $ourText = $divisionText;
            $ourText = str_replace(' class=""', '', $ourText);
            $ourText = str_replace(' pwmotiontext="yes"', '', $ourText);
        }
        $ourText = add_motion_missing_wrappers($ourText, $divisionTitle);

        return [
            'title' => extract_title_from_wiki_text($ourText),
            'motionTextForEdit' => extract_motion_text_from_wiki_text_for_edit($ourText),
            'motionText' => extract_motion_text_from_wiki_text($ourText),
            'actionText' => extract_action_text_from_wiki_text($ourText),
            'wikiText' => $ourText
        ];
    }

    /**
     * Pass it through version 1 to extract how it would handle it.
     *
     * @param array<string,string> $migrationRow Data from the database.
     * @return array<string,string> Extracted Data.
     */
    public function getResultsFromV1(array $migrationRow): array
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
            $ldalink = 'http://services.parliament.uk/LordsDivisionsAnalysis/session/' .
                $ldasess .
                '/division/' .
                $ldadate .
                $ldanum;
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
}
