<?php

namespace PublicWhip\Providers;


use Psr\Log\LoggerInterface;

/**
 * Class WikiParserProvider.
 *
 * @package PublicWhip\Providers
 */
interface WikiParserProviderInterface
{
    /**
     * WikiParserProvider constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger);

    /**
     * Get the division title.
     * @param string $wiki The wiki text.
     * @param string $default Default title to return.
     * @return string
     */
    public function parseDivisionTitle(string $wiki, string $default): string;

    /**
     * Get the motion text from the wiki - suitable for editing.
     * @param string $wiki
     * @param string $default
     * @return string
     */
    public function parseMotionTextForEdit(string $wiki, string $default): string;

    /**
     * Parse the motion text suitable for display.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/wiki.inc#L112
     *
     * @param string $wiki
     * @param string $default
     * @return string
     */
    public function parseMotionText(string $wiki, string $default): string;

    /**
     * Cleans the HTML.
     * @param string $html
     * @return string
     */
    public function cleanHtml(string $html) : string;

    /**
     * Takes our safe html and converts it to normal html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L418
     * @param string $html
     * @return string|string[]|null
     */
    public function safeHtmlToNormalHtml(string $html);

    /**
     * Strips bad html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L313
     * @param string $text
     * @return string
     */
    public function stripBadHtml(string $text): string;

    /**
     * Only keeps approved attributes of HTML.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L334
     * @param array $arr
     * @return string
     */
    public function filterHtmlAttributes(array $arr): string;
}
