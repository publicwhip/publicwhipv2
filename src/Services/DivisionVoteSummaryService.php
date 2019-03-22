<?php
declare(strict_types=1);

namespace PublicWhip\Services;

use Psr\Log\LoggerInterface;
use PublicWhip\Entities\DivisionVoteSummary;
use PublicWhip\Factories\EntityFactoryInterface;
use PublicWhip\Providers\DatabaseProviderInterface;

class DivisionVoteSummaryService implements DivisionVoteSummaryServiceInterface
{
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
     *  The entity factory.
     *
     * @var EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * Setup the division service.
     *
     * @param DatabaseProviderInterface $databaseProvider Database connection layer.
     * @param EntityFactoryInterface $entityFactory Entity factory.
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(
        DatabaseProviderInterface $databaseProvider,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
    }

    /**
     * Get the division vote summary or null if not found.
     *
     * @param int $divisionId Division id we are fetching for.
     * @return DivisionVoteSummary|null
     */
    public function getForDivision(int $divisionId) : ?DivisionVoteSummary
    {
        $data = $this->databaseProvider->table('pw_cache_divinfo')
            ->select([
                'division_id as divisionId',
                'rebellions',
                'tells AS tellers',
                'turnout',
                'possible_turnout as possibleTurnout',
                'aye_majority as ayeMajority'
            ])
            ->where('division_id', '=', $divisionId)
            ->first();

        if (null === $data) {
            $this->logger->debug(
                __METHOD__ . ': Did not find by id {id}',
                [
                    'id' => $divisionId
                ]
            );

            return null;
        }

        return $this->entityFactory->divisionVoteSummary(get_object_vars($data));
    }
}
