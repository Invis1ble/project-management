<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Project;

trait ProjectResponseFixtureTrait
{
    public function projectResponseFixture(): array
    {
        $project = file_get_contents(__DIR__ . '/fixture/response/project.200.json');

        return json_decode($project, true);
    }
}
