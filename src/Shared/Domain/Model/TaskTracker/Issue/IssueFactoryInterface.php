<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

interface IssueFactoryInterface
{
    public function createIssue(
        int $id,
        string $key,
        string $typeId,
        string $summary,
        array $sprints,
    ): Issue;
}
