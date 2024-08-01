<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Application\Saga;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\Tests\Shared\Application\Saga\PublicationSagaTestCase;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Version\VersionResponseFixtureTrait;

abstract class ReleaseSagaTestCase extends PublicationSagaTestCase
{
    use VersionResponseFixtureTrait;

    protected function assertReleasePublicationStatusChanged(
        object $event,
        StatusInterface $expectedPreviousStatus,
        StatusInterface $expectedStatus,
    ): void {
        $this->assertInstanceOf(ReleasePublicationStatusChanged::class, $event);
        $this->assertObjectEquals($expectedPreviousStatus, $event->previousStatus);
        $this->assertObjectEquals($expectedStatus, $event->status);
    }
}
