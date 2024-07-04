<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Version;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

trait VersionResponseFixtureTrait
{
    public function versionResponseFixture(
        Version\Name $versionName,
        bool $released,
    ): array {
        $version = file_get_contents(__DIR__ . '/fixture/response/version.200.json');
        $version = json_decode($version, true);

        $version['name'] = (string) $versionName;
        $version['released'] = $released;

        return $version;
    }
}
