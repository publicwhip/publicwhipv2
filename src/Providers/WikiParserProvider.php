<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Providers\WikiFailedRegExp;

/**
 * Class WikiParserProvider.
 *
 * @package PublicWhip\Providers
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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the division title.
     * @param string $wiki The wiki text.
     * @param string $default Default title to return.
     * @return string
     */
    public function parseDivisionTitle(string $wiki, string $default): string
    {
        if (preg_match('/--- DIVISION TITLE ---(.*)--- MOTION EFFECT/', $wiki, $matches)) {
            return $matches[1];
        }
        return $default;
    }

    /**
     * Get the motion text from the wiki - suitable for editing.
     * @param string $wiki
     * @param string $default
     * @return string
     */
    public function parseMotionTextForEdit(string $wiki, string $default): string
    {
        if (preg_match('/--- MOTION EFFECT ---(.*)--- COMMENT/s', $wiki, $matches)) {
            $text = $matches[1];
            $text = preg_replace(
                "/<p\b.*?class=\"italic\".*?>(.*)<\/p>/",
                '<p><i>\\1</i></p>',
                $text
            );
            if (null === $text) {
                throw new WikiFailedRegExp(
                    sprintf(
                        'Failed with %s',
                        array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                    )
                );
            }
            $text = preg_replace(
                "/<p\b.*?class=\"indent\".*?>(.*)<\/p>/",
                '<blockquote>\\1</blockquote>',
                $text
            );
            if (null === $text) {
                throw new WikiFailedRegExp(
                    sprintf(
                        'Failed with %s',
                        array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                    )
                );
            }
            if (!is_string($text)) {
                throw new WikiFailedRegExp('Expected string');
            }
            return $text;
        }
        return $default;
    }

    /**
     * Parse the motion text suitable for display.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/wiki.inc#L112
     *
     * @param string $wiki
     * @param string $default
     * @return string
     */
    public function parseMotionText(string $wiki, string $default): string
    {
        $motion = $this->parseMotionTextForEdit($wiki, $default);
        if (!preg_match('/<\/.*?>/', $motion)) {
            $motionLines = explode("\n", $motion);
            $binUL = 0;
            $res = [];
            $matches = [];
            $footerNumber = 0;
            foreach ($motionLines as $motionLine) {
                $motionLine = preg_replace("/''(.*?)''/", '<em>\\1</em>', $motionLine);
                if (null === $motionLine) {
                    throw new WikiFailedRegExp(
                        sprintf(
                            'Failed with %s',
                            array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                        )
                    );
                }
                $motionLine = preg_replace(
                    "/\[(https?:\S*)\s+(.*?)\]/",
                    '<a href="\\1">\\2</a>',
                    $motionLine
                );
                if (null === $motionLine) {
                    throw new WikiFailedRegExp(
                        sprintf(
                            'Failed with %s',
                            array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                        )
                    );
                }
                $motionLine = preg_replace(
                    "/(?<![*\s])(\[(\d+)\])/",
                    '<sup class="sup-\\2">' .
                    '<a class="sup" href="#footnote-\\2" onclick="ClickSup(\\2); return false;">' .
                    '\\1' .
                    '</a></sup>',
                    $motionLine
                );
                if (null === $motionLine) {
                    throw new WikiFailedRegExp(
                        sprintf(
                            'Failed with %s',
                            array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                        )
                    );
                }
                if (preg_match("/^\s\s*$/", $motionLine)) {
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
                        $footerNumber = preg_replace(
                            '/[\s\*\[\]]+/',
                            '',
                            $matches[0]
                        );
                        if (null === $footerNumber) {
                            throw new WikiFailedRegExp(
                                sprintf(
                                    'Failed with %s',
                                    array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                                )
                            );
                        }
                        if (!is_string($footerNumber)) {
                            throw new WikiFailedRegExp('Expected a string');
                        }
                        $footerNumber = (int)$footerNumber;
                    }
                    $motionLine = preg_replace('/^(\*\*|\*|:)\s*/', '', $motionLine);
                    if (null === $motionLine) {
                        throw new WikiFailedRegExp(
                            sprintf(
                                'Failed with %s',
                                array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                            )
                        );
                    }
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

                if (0 === $binUL) {
                    $res[] = '</p>';
                } else {
                    $res[] = '</li>';
                }
            }
            if ($binUL) {
                $res[] = '</ul>';
            }
            $motion = implode("\n", $res);
        }
        $motion = $this->cleanHtml($motion);
        return $motion;
    }

    /**
     * Cleans the HTML.
     * @param string $html
     * @return string
     */
    public function cleanHtml(string $html): string
    {
        return $this->safeHtmlToNormalHtml($this->stripBadHtml($html));
    }

    /**
     * Takes our safe html and converts it to normal html.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L418
     * @param string $html
     * @return string
     */
    public function safeHtmlToNormalHtml(string $html) : string
    {
        $patterns = [
            "/&amp;(#?[A-Za-z0-9]+?;)/",
            '/' . preg_quote(self::SAFE_LESSTHAN, '/') . '/',
            '/' . preg_quote(self::SAFE_GREATERTHAN, '/') . '/',
            '/' . preg_quote(self::SAFE_QUOTE, '/') . '/'
        ];
        $replace = ['&1', '<', '>', '"'];
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
     * @param string $text
     * @return string
     */
    public function stripBadHtml(string $text): string
    {
        $htmlTagsAllowed = ['a', 'b', 'i', 'p', 'ol', 'ul', 'li', 'blockquote', 'br', 'em', 'sup', 'sub'];
        $htmlRegExp = implode('|', $htmlTagsAllowed);
        $htmlAllowedStripTags = '<' . implode('><', $htmlTagsAllowed) . '>';
        $checkedText = strip_tags($text, $htmlAllowedStripTags);
        $checkedText = preg_replace_callback(
            '/<(' . $htmlRegExp . ')\b(.*?)>/si',
            [$this, 'filterHtmlAttributes'],
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
        $checkedText = preg_replace(
            '/<\/([^ ' . "\n" . '>]+)[^>]*>/i',
            self::SAFE_LESSTHAN . '/$1' . self::SAFE_GREATERTHAN,
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
        $checkedText = preg_replace('#^\s+$#m', '', $checkedText);
        if (null === $checkedText) {
            throw new WikiFailedRegExp(
                sprintf(
                    'Failed with %s',
                    array_flip(get_defined_constants(true)['pcre'])[preg_last_error()]
                )
            );
        }
        $checkedText = htmlentities($checkedText, ENT_COMPAT, 'UTF-8');
        return $checkedText;
    }

    /**
     * Only keeps approved attributes of HTML.
     *
     * @see https://github.com/publicwhip/publicwhip/blob/a4899135b6957abae85da3fc93c4cc3cf9e4fbc1/website/pretty.inc#L334
     * @param array $arr
     * @return string
     */
    public function filterHtmlAttributes(array $arr): string
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
            $prepared =
                array_merge($prepared, str_replace('"', self::SAFE_QUOTE, $matches[0]));
            preg_match_all("/(?:$noSpecial)\s*=\s*'[^\s'>]+'/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
            preg_match_all("/(?:$noSpecial)\s*=\s*[^\s>'\"][^\s>]*/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
        }
        if ($special) {
            preg_match_all("/(?:$special)\s*=\s*\"[^\"]*\"/is", $attributes, $matches);
            $prepared =
                array_merge($prepared, str_replace('"', self::SAFE_QUOTE, $matches[0]));
            preg_match_all("/(?:$special)\s*=\s*'[^']*'/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
            preg_match_all("/(?:$special)\s*=\s*[^\s>'\"][^\s>]*/is", $attributes, $matches);
            $prepared = array_merge($prepared, $matches[0]);
        }
        $outText = implode(' ', $prepared);
        return self::SAFE_LESSTHAN . $element . ($outText ? ' ' . $outText : '') . self::SAFE_GREATERTHAN;
    }
}
