<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\ContinuousIntegration\Pipeline;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\LatestPipelineStuck;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<LatestPipelineStuck>
 */
class LatestPipelineStuckSerializationTest extends SerializationTestCase
{
    protected function createObject(): LatestPipelineStuck
    {
        return new LatestPipelineStuck(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            pipelineId: Pipeline\PipelineId::from(2),
            status: Pipeline\Status::Running,
            guiUrl: new Uri('http://127.0.0.1:3000/test-group/test-project/-/pipelines/2'),
            maxAwaitingTime: new \DateInterval('PT30M'),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        if ($object1->projectId->equals($object2->projectId)
            && $object1->ref->equals($object2->ref)
            && $object1->pipelineId->equals($object2->pipelineId)
            && $object1->status->equals($object2->status)
            && (string) $object1->guiUrl === (string) $object2->guiUrl
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
