<?php
declare(strict_types = 1);

namespace PublicWhip\Services;

use Psr\Log\LoggerInterface;
use PublicWhip\Entities\MotionEntity;
use PublicWhip\Exceptions\Services\BadDatabaseReturnException;
use PublicWhip\Factories\EntityFactoryInterface;
use PublicWhip\Providers\DatabaseProviderInterface;
use PublicWhip\Providers\WikiParserProviderInterface;

/**
 * Reads/writes motions.
 *
 * These are stored in the database as 'wiki' entries.
 */
class MotionService implements MotionServiceInterface
{
    /**
     *  The entity factory.
     *
     * @var EntityFactoryInterface
     */
    private $entityFactory;

    /**
     *  The database layer.
     *
     * @var DatabaseProviderInterface $databaseProvider
     */
    private $databaseProvider;

    /**
     *  The logger.
     *
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * The wiki parser.
     *
     * @var WikiParserProviderInterface
     */
    private $wikiParserProvider;

    /**
     * Setup.
     *
     * @param DatabaseProviderInterface $databaseProvider Database connection layer.
     * @param EntityFactoryInterface $entityFactory Entity factory.
     * @param LoggerInterface $logger Logger.
     * @param WikiParserProviderInterface $wikiParserProvider The wiki parser.
     */
    public function __construct(
        DatabaseProviderInterface $databaseProvider,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        WikiParserProviderInterface $wikiParserProvider
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->wikiParserProvider = $wikiParserProvider;
    }

    /**
     * Get the latest motion text for a division.
     *
     * @param int $divisionId The division id.
     * @return MotionEntity
     */
    public function getLatestForDivision(int $divisionId): MotionEntity
    {
        $divisionData = $this->databaseProvider
            ->table('pw_division')
            ->select([
                'division_id',
                'division_date',
                'division_number',
                'house',
                'motion',
                'division_name'
            ])
            ->where('division_id', '=', $divisionId)
            ->first();

        if (null === $divisionData) {
            $this->logger->warning(
                'Could not find division id {divisionId}',
                ['divisionId' => $divisionId]
            );
            throw new BadDatabaseReturnException('Could not find division for motion');
        }

        /**
         * Get the basic division data.
         *
         * @var object|null $basicDivision
         */

        $wiki = $this->databaseProvider
            ->table('pw_dyn_wiki_motion')
            ->select([
                'text_body AS wikiText',
                'wiki_id AS id',
                'user_id AS lastEditedByUserId',
                'edit_date AS lastEditDateTime'
            ])
            ->where('division_date', '=', $divisionData->division_date)
            ->where('division_number', '=', $divisionData->division_number)
            ->where('house', '=', $divisionData->house)
            ->orderBy('id', 'DESC')
            ->first();

        $wikiText = $this->wikiParserProvider->toWiki(
            $divisionData->division_name,
            $divisionData->motion,
            $wiki ? $wiki->wikiText : null
        );

        $actionText = $this->wikiParserProvider->parseActionTextFromWiki($wikiText);

        /** @var MotionEntity $motion */
        $motion = $this->entityFactory->motion(
            [
                'divisionId' => $divisionData->division_id,
                'title' => $this->wikiParserProvider->parseDivisionTitleFromWiki($wikiText),
                'motion' => $this->wikiParserProvider->parseMotionTextFromWiki($wikiText),
                'comments' => $this->wikiParserProvider->parseCommentTextFromWiki($wikiText),
                'ayeSummary' => $actionText['aye'] ?? '',
                'noeSummary' => $actionText['no'] ?? '',
                'id' => $wiki ? $wiki->id : null,
                'lastEditedByUserId' => $wiki ? $wiki->lastEditedByUserId : null,
                'lastEdited' => $wiki ? $wiki->lastEditDateTime : null
            ]
        );

        return $motion;
    }
}
