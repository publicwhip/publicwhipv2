<?php
declare(strict_types = 1);

namespace PublicWhip\v1migrationUtils;

/**
 * Generates the test data for the wiki HTML markup tests.
 */

require_once __DIR__ . '/../originalPublicWhipCode/website/pretty.inc';

$inputHtml = [
    '<p>This is a test with paragraph markers</p>',
    'This has a pound symbol as a decimal code &#163; and as the entity &pound; and as itself ' . mb_chr(163, 'utf-8'),
    'This has "double quote marks" as the &quot;entity&quot; and the &#34;decimal code&#34;',
    'This has \'single quote marks aka apostrophe\; as the &apos;entity&apos; and the &#39;decimal code&#39;',
    'This has the ampersand as a decimal code &#32; and as the entity &amp; and as itself &.',
    'This has the greater than symbol as a decimal code &#62; and as entity &gt; and as itself >',
    'This has the less than symbol as a decimal code &#60; and as entity &lt; and as itself <',
    'This has an <madeup>invented</madeup> tag which should be stripped',
    'This has a link <a href="https://www.example.com" invented="result">which should keep only the href</a>.',
    'This has mdash as the html entity &mdash; the hex code &#x2014;, the decimal code &#8212; '
    . 'and as the character ' . mb_chr(8212, 'utf-8')
];
$out = [];

foreach ($inputHtml as $line) {
    $guyed = guy_strip_bad($line);
    $out[] = [
        'input' => $line,
        'safeHtml' => $guyed,
        'normalHtml' => guy2html($guyed)
    ];
}

file_put_contents(
    __DIR__ .
    DIRECTORY_SEPARATOR . '..' .
    DIRECTORY_SEPARATOR . 'tests' .
    DIRECTORY_SEPARATOR . 'Unit' .
    DIRECTORY_SEPARATOR . 'Providers' .
    DIRECTORY_SEPARATOR . 'WikiParserProvider' .
    DIRECTORY_SEPARATOR . 'html.json',
    json_encode($out, JSON_PRETTY_PRINT)
);
