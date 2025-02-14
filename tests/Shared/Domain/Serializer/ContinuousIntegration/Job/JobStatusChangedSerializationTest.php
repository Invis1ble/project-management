<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\ContinuousIntegration\Job;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<JobStatusChanged>
 */
class JobStatusChangedSerializationTest extends SerializationTestCase
{
    protected function createObject(): JobStatusChanged
    {
        return new JobStatusChanged(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            pipelineId: Pipeline\PipelineId::from(3),
            jobId: Job\JobId::from(4),
            name: Job\Name::fromString('deploy'),
            previousStatus: new Job\Status\StatusPreparing(),
            status: new Job\Status\StatusPending(),
            guiUrl: new Uri('https://example.com/foo/bar/-/jobs/8'),
            createdAt: new \DateTimeImmutable('-1 hour'),
            startedAt: new \DateTimeImmutable('-30 minutes'),
            finishedAt: new \DateTimeImmutable(),
            maxAwaitingTime: new \DateInterval('PT30M'),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        if ($object1->projectId->equals($object2->projectId)
            && $object1->ref->equals($object2->ref)
            && $object1->pipelineId->equals($object2->pipelineId)
            && $object1->jobId->equals($object2->jobId)
            && $object1->name->equals($object2->name)
            && $object1->previousStatus->equals($object2->previousStatus)
            && $object1->status->equals($object2->status)
            && $object1->guiUrl === $object2->guiUrl
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->createdAt == $object2->createdAt
            && $object1->startedAt == $object2->startedAt
            && $object1->finishedAt == $object2->finishedAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
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
