<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\State;

trait IssuesResponseFixtureTrait
{
    public function issuesResponseFixture(
        Issue\Key $issueKey,
        Issue\Summary $issueSummary,
        Board\BoardId $issueBoardId,
        string $sprintFieldId,
    ): array {
        $issues = file_get_contents(__DIR__ . '/fixture/response/issues.200.json');
        $issues = json_decode($issues, true);

        $issues['issues'][0]['key'] = (string) $issueKey;
        $issues['issues'][0]['fields'] = [
            'summary' => (string) $issueSummary,
            "customfield_$sprintFieldId" => [
                [
                    'boardId' => $issueBoardId->value(),
                    'name' => 'June 2024 1-2',
                    'state' => State::Active->value,
                ],
            ],
        ] + $issues['issues'][0]['fields'];

        return $issues;
    }
}
