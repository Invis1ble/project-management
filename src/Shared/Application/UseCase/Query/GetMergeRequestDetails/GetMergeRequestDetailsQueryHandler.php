<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetMergeRequestDetails;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

final readonly class GetMergeRequestDetailsQueryHandler implements QueryHandlerInterface
{
    public function __construct(private MergeRequestManagerInterface $mergeRequestManager)
    {
    }

    public function __invoke(GetMergeRequestDetailsQuery $query): Details
    {
        return $this->mergeRequestManager->details($query->projectId, $query->mergeRequestIid);
    }
}
