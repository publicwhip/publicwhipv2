<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Providers\WikiFailedRegExp;
use function is_numeric;

/**
 * Class WikiParserProvider.
 *
 * Turn off all warnings as this code is a bit of a mess, even though it's been tidied up since v1.
 *
 * @SuppressWarnings(PHPMD)
 */
final class WikiParserProvider implements WikiParserProviderInterface
{

    /**
     * A 'safe' version of html &lt; used for tag stripping.
     */
    private const SAFE_LESSTHAN = '[[[';

    /**
     * 'safe' version of html &gt; used for tag stripping.
     */
    private const SAFE_GREATERTHAN = ']]]';

    /**
     *  'safe' version of html &quot; used for tag stripping.
     */
    private const SAFE_QUOTE = '{|}';

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * WikiParserProvider constructor.
     *
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the division title.
     *
     * @param string $wiki The wiki text.
     * @param string $default Default title to return.
     *
     * @return string
     */
    public function parseDivisionTitle(string $wiki, string $default): string
    {
        $newTitle = $default;
        if ('' !== $wiki && preg_match('/--- DIVISION TITLE ---\s*(.*)\s*--- MOTION EFFECT/s', $wiki, $matches)) {
            $newTitle = $matches[1];
            $this->logger->debug('Extracted wiki title {wikiTitle}', ['wikiTitle' => $newTitle]);
        } else {
            $this->logger->debug('No wiki text found, using default title: {title}', ['default' => $default]);
        }
        $newTitle = trim(strip_tags($newTitle));
        $newTitle = str_replace(' - ', ' &#8212; ', $newTitle);
        return $newTitle;
    }

    /**
     * Parse the motion text suitable for display.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/wiki.inc#L112
     *
     * @param string $wiki The text to parse.
     * @param string $default The text to return if it's not a valid wiki text.
     *
     * @return string
     */
    public function parseMotionText(string $wiki, string $default): string
    {
        $this->logger->debug('Going to parse motion text');
        $motion = $this->parseMotionTextForEdit($wiki, $default);
        if (!preg_match('/<\/.*?>/', $motion)) {
            $motionLines = explode("\n", $motion);
            $binUL = 0;
            $res = [];
            $matches = [];
            $footerNumber = 0;
            foreach ($motionLines as $motionLine) {
                $motionLine = $this->replaceUsingRegExpStrings("/''(.*?)''/", '<em>\\1</em>', $motionLine);
                $motionLine = $this->replaceUsingRegExpStrings(
                    '/\[(https?:\S*)\s+(.*?)\]/',
                    '<a href="\\1">\\2</a>',
                    $motionLine
                );
                $motionLine = $this->replaceUsingRegExpStrings(
                    '/(?<![*\s])(\[(\d+)\])/',
                    '<sup class="sup-\\2">' .
                    '<a class="sup" href="#footnote-\\2" onclick="ClickSup(\\2); return false;">' .
                    '\\1' .
                    '</a></sup>',
                    $motionLine
                );
                if (preg_match('/^\s\s*$/', $motionLine)) {
                    continue;
                }
                // skip comment lines we lift up for the short sentences
                if (preg_match('/^@/', $motionLine)) {
                    continue;
                }
                if (preg_match('/^([\*|:])/', $motionLine)) {
                    if (!$binUL) {
                        $res[] = '<ul>';
                    }
                    $binUL = (preg_match('/^\*\*/', $motionLine) ? 2 : 1);
                    if (preg_match('/^:/', $motionLine)) {
                        $binUL = 3;
                    } elseif (preg_match('/^\s*\*\s*\[\d+\]/', $motionLine, $matches)) {
                        $binUL = 4;
                        $footerNumber = $this->replaceUsingRegExpStrings(
                            '/[\s\*\[\]]+/',
                            '',
                            $matches[0]
                        );
                        if (!is_numeric($footerNumber)) {
                            throw new WikiFailedRegExp('Expected something that looked numerical');
                        }
                        $footerNumber = (int)$footerNumber;
                    }
                    $motionLine = $this->replaceUsingRegExpStrings('/^(\*\*|\*|:)\s*/', '', $motionLine);
                } elseif (0 !== $binUL) {
                    $binUL = 0;
                    $res[] = '</ul>';
                }

                switch ($binUL) {
                    case 0:
                        $res[] = '<p>';
                        break;
                    case 2:
                        $res[] = '<li class="house">';
                        break;
                    case 3:
                        $res[] = '<li class="block">';
                        break;
                    case 4:
                        $res[] = '<li class="footnote" id="footnote-' . $footerNumber . '">';
                        break;
                    default:
                        $res[] = '<li>';
                        break;
                }

                $res[] = $motionLine;

                $res[] = (0 === $binUL) ? '</p>' : '</li>';
            }
            if ($binUL) {
                $res[] = '</ul>';
            }
            $motion = implode("\n", $res);
        }
        $this->logger->debug(
            'Making safe motion text: {motion}',
            ['motion' => htmlspecialchars($motion, ENT_QUOTES)]
        );

        $motion = $this->safeHtmlToNormalHtml($this->htmlToSafeHtml(trim($motion)));
        $this->logger->debug(
            'Returning motion text: {motion}',
            [
                'motion' => htmlspecialchars($motion, ENT_QUOTES)
            ]
        );

        return $motion;
    }

    /**
     * Replace text using a regular expression, logging/raising error if appropriate.
     *
     * @param string $pattern Search pattern
     * @param string $replacement Replacement text
     * @param string $inputString Input string.
     *
     * @return string
     *
     * @throws WikiFailedRegExp
     */
    private function replaceUsingRegExpStrings(string $pattern, string $replacement, string $inputString): string
    {
        $output = preg_replace($pattern, $replacement, $inputString);
        if (null === $output) {
            $error = array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
            $this->logger->error(
                'Failed to replace using regular expression ({error}) using pattern {pattern} on {input}',
                [
                    'pattern' => $pattern,
                    'replacement' => $replacement,
                    'error' => $error,
                    'input' => $inputString
                ]
            );
            throw new WikiFailedRegExp(sprintf('Failed with %s', $error));
        }
        return $output;
    }

    /**
     * Get the motion text from the wiki - suitable for editing.
     *
     * PublicWhip v1 function extract_motion_text_from_wiki_text_for_edit
     *
     * @param string $wiki Wiki text to parse.
     * @param string $default If the wiki text was not valid, the text to be returned instead.
     *
     * @return string
     */
    public function parseMotionTextForEdit(string $wiki, string $default): string
    {
        $text = $default;
        if ('' !== $wiki && preg_match('/--- MOTION EFFECT ---(.*)--- COMMENT/s', $wiki, $matches)) {
            $text = $matches[1];
            $this->logger->debug('Will be using extracted text from wiki');
        }
        $this->logger->debug('Parsing text {text}', ['text' => htmlspecialchars($text, ENT_QUOTES)]);
        // strip empty items.
        $text = str_replace(['&#8212;',' class=""', ' pwmotiontext="yes"'], ['-','',''], trim($text));
        $text = $this->replaceUsingRegExpStrings(
            "/<p\b.*?class=\"italic\".*?>(.*)<\/p>/",
            '<p><i>\\1</i></p>',
            $text
        );
        $text = $this->replaceUsingRegExpStrings(
            "/<p\b.*?class=\"indent\".*?>(.*)<\/p>/",
            '<blockquote>\\1</blockquote>',
            $text
        );
        $this->logger->debug(
            'Returning cleaned up text for edit {editText}',
            [
                'editText' => htmlspecialchars($text, ENT_QUOTES)
            ]
        );
        return trim($text);
    }

    /**
     * Takes our safe html and converts it to normal html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L418
     *
     * @param string $html The 'safe' html to convert back to normal html.
     *
     * @return string
     */
    public function safeHtmlToNormalHtml(string $html): string
    {
        $patterns = [
            '/&amp;(#?[A-Za-z0-9]+?;)/',
            '/' . preg_quote(self::SAFE_LESSTHAN, '/') . '/',
            '/' . preg_quote(self::SAFE_GREATERTHAN, '/') . '/',
            '/' . preg_quote(self::SAFE_QUOTE, '/') . '/'
        ];
        $replace = ['&\\1', '<', '>', '"'];
        $return = preg_replace($patterns, $replace, $html);
        if (null === $return) {
            throw new WikiFailedRegExp(
                sprintf(
                    'Failed with %s',
                    array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                )
            );
        }
        return $return;
    }

    /**
     * Strips bad html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L313
     *
     * @param string $text Text to strip unwanted html from.
     *
     * @return string
     */
    public function htmlToSafeHtml(string $text): string
    {
        $this->logger->debug(
            'Stripping bad html from {html}',
            ['html' => htmlspecialchars($text, ENT_QUOTES)]
        );
        $htmlTagsAllowed = ['a', 'b', 'i', 'p', 'ol', 'ul', 'li', 'blockquote', 'br', 'em', 'sup', 'sub'];
        $htmlRegExp = implode('|', $htmlTagsAllowed);
        $htmlAllowedStripTags = '<' . implode('><', $htmlTagsAllowed) . '>';
        $checkedText = strip_tags($text, $htmlAllowedStripTags);
        $checkedText = preg_replace_callback(
            '/<(' . $htmlRegExp . ')\b(.*?)>/si',
            function (array $data): string {
                return self::filterHtmlAttributes($data);
            },
            $checkedText
        );
        if (null === $checkedText) {
            throw new WikiFailedRegExp(
                sprintf(
                    'Failed with %s',
                    array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                )
            );
        }
        $checkedText = $this->replaceUsingRegExpStrings(
            '/<\/([^ ' . "\n" . '>]+)[^>]*>/i',
            self::SAFE_LESSTHAN . '/$1' . self::SAFE_GREATERTHAN,
            $checkedText
        );
        $checkedText = $this->replaceUsingRegExpStrings('#^\s+$#m', '', $checkedText);
        $checkedText = htmlentities($checkedText, ENT_COMPAT, 'UTF-8');
        $this->logger->debug(
            'Returning checked text {checkedText}',
            ['checkedText' => htmlspecialchars($checkedText, ENT_QUOTES)]
        );

        return $checkedText;
    }

    /**
     * Only keeps approved attributes of HTML.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L334
     *
     * @param string[] $arr Html element and items to strip away.
     *
     * @return string
     */
    private static function filterHtmlAttributes(array $arr): string
    {
        $element = strtolower($arr[1]);
        $attributes = $arr[2];
        $noSpecial = '';
        $special = 'title|class|id|onclick';
        $prepared = [];

        switch ($element) {
            case 'a':
                $noSpecial = 'href|hreflang|name|lang';
                break;
            case 'img':
                $noSpecial = 'longdesc|src|align';
                $special .= '|class|alt';
                if (false !== stripos($attributes, 'alt')) {
                    $prepared = ['alt=' . self::SAFE_QUOTE . self::SAFE_QUOTE];
                }
                break;
            case 'ins':
            case 'del':
                $noSpecial = 'cite|datetime|lang';
                break;
            case 'blockquote':
            case 'q':
                $noSpecial = 'cite|lang';
                break;
            case 'br':
            case 'news':
            case 'column':
                $special = '';
                break;
            case 'span':
                $special .= '|class';
                $noSpecial = 'lang';
                break;
            case 'form':
                $special = '';
                $noSpecial = 'level';
                break;
            case 'ol':
                $noSpecial = 'type';
                break;
            default:
                $noSpecial = 'lang';
        }

        if ($noSpecial) {
            preg_match_all("/(?:$noSpecial)\s*=\s*\"[^\s\">]+\"/is", $attributes, $matches);
            $prepared = array_merge($prepared, str_replace('"', self::SAFE_QUOTE, $matches[0]));
            preg_match_all("/(?:$noSpecial)\s*=\s*'[^\s'>]+'/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
            preg_match_all("/(?:$noSpecial)\s*=\s*[^\s>'\"][^\s>]*/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
        }
        if ($special) {
            preg_match_all("/(?:$special)\s*=\s*\"[^\"]*\"/is", $attributes, $matches);
            $prepared = array_merge($prepared, str_replace('"', self::SAFE_QUOTE, $matches[0]));
            preg_match_all("/(?:$special)\s*=\s*'[^']*'/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
            preg_match_all("/(?:$special)\s*=\s*[^\s>'\"][^\s>]*/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
        }
        $outText = implode(' ', $prepared);
        return self::SAFE_LESSTHAN . $element . ($outText ? ' ' . $outText : '') . self::SAFE_GREATERTHAN;
    }
}
