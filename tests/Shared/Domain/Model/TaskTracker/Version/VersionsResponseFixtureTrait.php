<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Version;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

trait VersionsResponseFixtureTrait
{
    public function versionsResponseFixture(
        Version\Name $latestVersionName,
    ): array {
        $versions = file_get_contents(__DIR__ . '/fixture/response/versions.200.json');
        $versions = json_decode($versions, true);

        $versions['values'][0]['name'] = (string) $latestVersionName;
        $versions['values'][0]['released'] = false;

        return $versions;
    }
}
