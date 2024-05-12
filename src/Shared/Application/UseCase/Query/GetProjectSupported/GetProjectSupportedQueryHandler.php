<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Application\UseCase\Query\GetProjectSupported;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;

final readonly class GetProjectSupportedQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MergeRequestManagerInterface $mergeRequestManager)
    {
    }

    public function __invoke(GetProjectSupportedQuery $query): bool
    {
        return $this->mergeRequestManager->supports($query->projectId);
    }
}
