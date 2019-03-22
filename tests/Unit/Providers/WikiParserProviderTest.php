<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Providers\WikiNotAWikiEntryException;
use PublicWhip\Providers\WikiParserProvider;
use PublicWhip\Providers\WikiParserProviderInterface;
use ReflectionException;
use RuntimeException;
use function is_array;
use function is_string;

/**
 * WikiParserProviderTest.
 *
 * @coversDefaultClass \PublicWhip\Providers\WikiParserProvider
 * @covers \PublicWhip\Providers\WikiParserProvider::<!public>
 */
final class WikiParserProviderTest extends TestCase
{
    /**
     * Cached division test data.
     *
     * "divisionId" = [ "something"=>"something","x"=>["a"=>"b"]]
     *
     * @var array<string, array<array<string,string|array<string, string>>>>|null $cachedDivisionTestData
     */
    private $cachedDivisionTestData;

    /**
     * Cached html markup test data.
     *
     * @var array<array<string,string>>|null $cachedHtmlMarkupTestData
     */
    private $cachedHtmlMarkupTestData;

    /**
     * Get the html markup test data.
     *
     * @return array<array<string,string>>
     */
    private function getHtmlMarkupTestData(): array
    {
        if (is_array($this->cachedHtmlMarkupTestData)) {
            return $this->cachedHtmlMarkupTestData;
        }

        $contents = file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . 'WikiParserProvider' . DIRECTORY_SEPARATOR . 'html.json'
        );

        if (!$contents) {
            throw new RuntimeException('Unable to load test data html.json');
        }

        $this->cachedHtmlMarkupTestData = json_decode($contents, true);

        if (!$this->cachedHtmlMarkupTestData) {
            throw new RuntimeException('Unable to parse test data html.json: ' . json_last_error_msg());
        }

        return $this->cachedHtmlMarkupTestData;
    }

    /**
     * Get the mock divisions.
     *
     * @return array<string, array<array<string,string|array<string, string>>>>
     */
    private function getMockDivisions(): array
    {
        if (is_array($this->cachedDivisionTestData)) {
            return $this->cachedDivisionTestData;
        }

        $contents = file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . 'WikiParserProvider' . DIRECTORY_SEPARATOR . 'mockDivisions.json'
        );

        if (!$contents) {
            throw new RuntimeException('Unable to load test data mockDivisions.json');
        }

        $this->cachedDivisionTestData = json_decode($contents, true);

        if (!$this->cachedDivisionTestData) {
            throw new RuntimeException('Unable to parse test data mockDivisions.json: ' . json_last_error_msg());
        }

        return $this->cachedDivisionTestData;
    }

    /**
     * Mainly used for static analysis checks.
     *
     * @param WikiParserProviderInterface $sut System under test used to generate the wiki text.
     * @param array<string,string|array<string,string>> $divisionEntry Division details for parsing.
     * @return string
     */
    private function divisionEntryToWiki(WikiParserProviderInterface $sut, array $divisionEntry): string
    {
        if (!isset($divisionEntry['divisionName'], $divisionEntry['motion'], $divisionEntry['wikiText'])) {
            self::fail('Invalid division entry');

            return '';
        }
        if (is_string($divisionEntry['divisionName']) &&
            is_string($divisionEntry['motion']) &&
            is_string($divisionEntry['wikiText'])) {
            return $sut->toWiki(
                $divisionEntry['divisionName'],
                $divisionEntry['motion'],
                $divisionEntry['wikiText']
            );
        }
        self::fail('Bad division entry');

        return '';
    }

    /**
     * Check we render all division titles the same as v1.
     *
     * @covers ::parseDivisionTitleFromWiki
     * @throws ReflectionException
     */
    public function testParseDivisionTitleFromWiki(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        foreach ($this->getMockDivisions() as $divisionId => $division) {
            foreach ($division as $divisionEntry) {
                $toWiki = $this->divisionEntryToWiki($sut, $divisionEntry);
                $newTitle = $sut->parseDivisionTitleFromWiki($toWiki);
                if (!is_array($divisionEntry['v1']) ||
                    !isset($divisionEntry['v1']['title']) ||
                    !is_string($divisionEntry['v1']['title'])) {
                    self::fail('No title found');

                    return;
                }
                self::assertSame(
                    $divisionEntry['v1']['title'],
                    $newTitle,
                    'When testing division ' . $divisionId
                );
            }
        }
    }

    /**
     * Check we render all division texts the same as v1.
     *
     * @throws ReflectionException
     *
     * @covers ::parseMotionTextFromWiki
     */
    public function testParseMotionTextFromWiki(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        /**
         * @var string $divisionId Division id.
         * @var array<array<string,string|array<string, string>>> $division Division data
         */
        foreach ($this->getMockDivisions() as $divisionId => $division) {
            foreach ($division as $divisionEntry) {
                $toWiki = $this->divisionEntryToWiki($sut, $divisionEntry);

                $text = $sut->parseMotionTextFromWiki($toWiki);
                if (!is_array($divisionEntry['v1']) ||
                    !isset($divisionEntry['v1']['motionText']) ||
                    !is_string($divisionEntry['v1']['motionText'])) {
                    self::fail('No motionText found');

                    return;
                }
                $expected = $divisionEntry['v1']['motionText'];
                self::assertSame(
                    $expected,
                    $text,
                    'When testing division ' . $divisionId
                );
            }
        }
    }

    /**
     * Check we render all division texts the same as v1.
     *
     * @throws ReflectionException
     *
     * @covers ::parseMotionTextFromWikiForEdit
     */
    public function testParseMotionTextFromWikiForEdit(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        /**
         * @var string $divisionId Division id.
         * @var array<array<string,string|array<string, string>>> $division Division data
         */
        foreach ($this->getMockDivisions() as $divisionId => $division) {
            foreach ($division as $divisionEntry) {
                $toWiki = $this->divisionEntryToWiki($sut, $divisionEntry);

                $text = $sut->parseMotionTextFromWikiForEdit($toWiki);
                if (!is_array($divisionEntry['v1']) ||
                    !isset($divisionEntry['v1']['motionTextForEdit']) ||
                    !is_string($divisionEntry['v1']['motionTextForEdit'])) {
                    self::fail('No motionTextForEdit found');

                    return;
                }
                $expected = $divisionEntry['v1']['motionTextForEdit'];
                self::assertSame(
                    $expected,
                    $text,
                    'When testing division ' . $divisionId
                );
            }
        }
    }

    /**
     * Check we render all division texts the same as v1.
     *
     * @throws ReflectionException
     *
     * @covers ::parseActionTextFromWiki
     */
    public function testParseActionTextFromWiki(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        /**
         * @var string $divisionId Division id.
         * @var array<array<string,string|array<string, string>>> $division Division data
         */
        foreach ($this->getMockDivisions() as $divisionId => $division) {
            foreach ($division as $divisionEntry) {
                $toWiki = $this->divisionEntryToWiki($sut, $divisionEntry);

                /** @var array<string,string> $array */
                $array = $sut->parseActionTextFromWiki($toWiki);
                if (!is_array($divisionEntry['v1']) ||
                    !isset($divisionEntry['v1']['actionText']) ||
                    !is_array($divisionEntry['v1']['actionText'])) {
                    self::fail('No actionText found');

                    return;
                }
                /** @var array<string,string> $expected */
                $expected = $divisionEntry['v1']['actionText'];
                unset($expected['title']);
                $expected = array_change_key_case($expected, CASE_LOWER);
                self::assertSame(
                    $expected,
                    $array,
                    'When testing division ' . $divisionId
                );
            }
        }
    }

    /**
     * @covers ::__construct
     * @covers ::parseDivisionTitleFromWiki
     * @throws ReflectionException
     */
    public function testParseDivisionTitleFromWikiInvalid(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);
        $this->expectException(WikiNotAWikiEntryException::class);
        $this->expectExceptionMessage('Invalid text passed');
        $sut->parseDivisionTitleFromWiki('garbage');
    }

    /**
     * @covers ::parseMotionTextFromWikiForEdit
     * @throws ReflectionException
     */
    public function testParseMotionFromWikiForEditInvalid(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);
        $this->expectException(WikiNotAWikiEntryException::class);
        $this->expectExceptionMessage('Invalid text passed');
        $sut->parseMotionTextFromWikiForEdit('garbage');
    }

    /**
     * @covers ::parseMotionTextFromWikiForEdit
     * @throws ReflectionException
     */
    public function testParseCommentTextFromWikiInvalid(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);
        $this->expectException(WikiNotAWikiEntryException::class);
        $this->expectExceptionMessage('Invalid text passed');
        $sut->parseCommentTextFromWiki('garbage');
    }

    /**
     * Checks we can convert back from safe html to normal html.
     *
     * @throws ReflectionException
     *
     * @covers ::safeHtmlToNormalHtml
     */
    public function testSafeHtmlToNormalHtml(): void
    {
        $testData = $this->getHtmlMarkupTestData();
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        foreach ($testData as $entry) {
            $output = $sut->safeHtmlToNormalHtml($entry['safeHtml']);
            self::assertSame($entry['normalHtml'], $output);
        }
    }

    /**
     * Checks we convert html to the expected safe format.
     *
     * @throws ReflectionException
     *
     * @covers ::htmlToSafeHtml
     */
    public function testHtmlToSafeHtml(): void
    {
        $testData = $this->getHtmlMarkupTestData();
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        foreach ($testData as $entry) {
            $output = $sut->htmlToSafeHtml($entry['input']);
            self::assertSame($entry['safeHtml'], $output);
        }
    }
}
