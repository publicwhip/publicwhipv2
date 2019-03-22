<?php
declare(strict_types = 1);

namespace PublicWhip\Exceptions\Providers;

/**
 * Thrown if the wiki provider is passed plain text where it is expecting a wiki.
 */
final class WikiNotAWikiEntryException extends AbstractProviderException
{
}
