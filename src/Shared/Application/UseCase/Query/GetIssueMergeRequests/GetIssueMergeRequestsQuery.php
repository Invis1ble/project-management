<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Application\UseCase\Query\GetIssueMergeRequests;

use Invis1ble\Messenger\Query\QueryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;

final readonly class GetIssueMergeRequestsQuery implements QueryInterface
{
    public function __construct(
        public IssueId $issueId,
    ) {
    }
}
