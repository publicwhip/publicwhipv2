<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PublicWhip\Providers\WikiParserProvider;
use ReflectionException;
use RuntimeException;
use function is_array;

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
     * @var array<int, array<string, array<string, string>>>|null $cachedDivisionTestData
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
     * @return array<int, array<string, array<string, string>>>
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
     * Check we render all division titles the same as v1.
     *
     * @covers ::parseDivisionTitle
     * @throws ReflectionException
     */
    public function testParseDivisionTitle(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        foreach ($this->getMockDivisions() as $divisionId => $divisionEntry) {
            $lastWiki = last($divisionEntry['wiki']);
            $newTitle = $sut->parseDivisionTitle(
                $lastWiki['text_body'] ?? '',
                $divisionEntry['division']['division_name']
            );
            self::assertSame(
                $divisionEntry['v1']['title'],
                $newTitle,
                'When testing division ' . $divisionId
            );
        }
    }

    /**
     * Check we render all division texts the same as v1.
     *
     * @throws ReflectionException
     *
     * @covers ::parseMotionText
     * @covers ::parseMotionTextForEdit
     */
    public function testParseMotionText(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new WikiParserProvider($logger);

        foreach ($this->getMockDivisions() as $divisionId => $divisionEntry) {
            // we are comparing the last entry.
            $lastWiki = last($divisionEntry['wiki']);
            $newTitle = $sut->parseMotionText(
                $lastWiki['text_body'] ?? '',
                $divisionEntry['division']['motion']
            );
            $expected = $divisionEntry['v1']['description'];
            self::assertSame(
                $expected,
                $newTitle,
                'When testing division ' . $divisionId
            );
        }
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
