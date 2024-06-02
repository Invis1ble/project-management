<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetIssueMergeRequests;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;

final readonly class GetIssueMergeRequestsQuery implements QueryInterface
{
    public function __construct(
        public IssueId $issueId,
    ) {
    }
}
