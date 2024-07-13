<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue;

trait IssueTransitionsResponseFixtureTrait
{
    public function issueTransitionsResponseFixture(string $transitionName): array
    {
        $issueTransitions = file_get_contents(__DIR__ . '/fixture/response/issue_transitions.200.json');
        $issueTransitions = json_decode($issueTransitions, true);
        $issueTransitions['transitions'][0]['name'] = $transitionName;

        return $issueTransitions;
    }
}
