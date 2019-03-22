<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

/**
 * Turn off all warnings as this code is a bit of a mess, even though it's been tidied up since v1.
 *
 * @SuppressWarnings(PHPMD)
 */
interface WikiParserProviderInterface
{
    /**
     * Get a standardised wiki string.
     *
     * @param string $divisionName The name of the division
     * @param string $motion The text of the motion
     * @param string|null $wikiText Wiki text if we have any (empty if not)
     * @return string
     */
    public function toWiki(string $divisionName, string $motion, ?string $wikiText = null): string;

    /**
     * Get the division title.
     *
     * @param string $wikiText The wiki text.
     * @return string
     */
    public function parseDivisionTitleFromWiki(string $wikiText): string;

    /**
     * Parse the motion text suitable for display.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/wiki.inc#L112
     * @param string $wikiText The text to parse.
     * @return string
     */
    public function parseMotionTextFromWiki(string $wikiText): string;

    /**
     * Get the motion text from the wiki - suitable for editing.
     * PublicWhip v1 function extract_motion_text_from_wiki_text_for_edit
     *
     * @param string $wikiText Wiki text to parse.
     * @return string
     */
    public function parseMotionTextFromWikiForEdit(string $wikiText): string;

    /**
     * Parse any comment text.
     *
     * @param string $wikiText The wiki text.
     * @param bool|null $removeDefaultText True to remove the default comment text.
     * @return string
     */
    public function parseCommentTextFromWiki(string $wikiText, ?bool $removeDefaultText = null): string;

    /**
     * Extract the action values.
     *
     * @param string $wikiText Wiki text.
     * @return array<string,string>
     */
    public function parseActionTextFromWiki(string $wikiText): array;

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
