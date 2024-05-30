<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetLatestTagToday;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;

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
