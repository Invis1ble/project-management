<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Event\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobStuck;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializationTestCase;

/**
 * @extends SerializationTestCase<JobStuck>
 */
class JobStuckTest extends SerializationTestCase
{
    protected function createObject(): JobStuck
    {
        return new JobStuck(
            projectId: Project\ProjectId::from(1),
            jobId: Job\JobId::from(3),
            maxAwaitingTime: new \DateInterval('PT30M'),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        if ($object1->projectId->equals($object2->projectId)
            && $object1->jobId->equals($object2->jobId)
        ) {
            return true;
        }

        $datetime = new \DateTimeImmutable();

        return
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            $datetime->add($object1->maxAwaitingTime) == $datetime->add($object2->maxAwaitingTime)
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
