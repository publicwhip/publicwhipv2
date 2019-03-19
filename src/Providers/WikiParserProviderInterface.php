<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use Psr\Log\LoggerInterface;

/**
 * WikiParserProvider.
 */
interface WikiParserProviderInterface
{
    /**
     * WikiParserProvider.
     *
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(LoggerInterface $logger);

    /**
     * Get the division title.
     *
     * @param string $wiki The wiki text.
     * @param string $default Default title to return.
     * @return string
     */
    public function parseDivisionTitle(string $wiki, string $default): string;

    /**
     * Get the motion text from the wiki - suitable for editing.
     *
     * @param string $wiki Wiki text to parse.
     * @param string $default If the wiki text was not valid, the text to be returned instead.
     * @return string
     */
    public function parseMotionTextForEdit(string $wiki, string $default): string;

    /**
     * Parse the motion text suitable for display.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/wiki.inc#L112
     * @param string $wiki The text to parse.
     * @param string $default The text to return if it's not a valid wiki text.
     * @return string
     */
    public function parseMotionText(string $wiki, string $default): string;

    /**
     * Takes our safe html and converts it to normal html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L418
     * @param string $html The 'safe' html to convert back to normal html.
     * @return string
     */
    public function safeHtmlToNormalHtml(string $html): string;

    /**
     * Strips bad html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L313
     * @param string $text Text to strip unwanted html from.
     * @return string
     */
    public function htmlToSafeHtml(string $text): string;
}
