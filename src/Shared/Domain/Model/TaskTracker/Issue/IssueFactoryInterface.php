<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

interface IssueFactoryInterface
{
    public function createIssue(
        int $id,
        string $key,
        string $typeId,
        bool $subtask,
        string $summary,
        array $sprints,
    ): Issue;
}
