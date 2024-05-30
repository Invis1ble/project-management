<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Application\UseCase\Query\GetLatestTagToday;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Tag;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;

final readonly class GetLatestTagTodayQueryHandler implements QueryHandlerInterface
{
    public function __construct(private SourceCodeRepositoryInterface $backendSourceCodeRepository)
    {
    }

    /**
     * @return ?Tag<VersionName>
     */
    public function __invoke(GetLatestTagTodayQuery $query): ?Tag
    {
        return $this->backendSourceCodeRepository->latestTagToday();
    }
}
