<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Event\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\PipelineRetried;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializationTestCase;

/**
 * @extends SerializationTestCase<PipelineRetried>
 */
class PipelineRetriedTest extends SerializationTestCase
{
    protected function createObject(): PipelineRetried
    {
        return new PipelineRetried(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            pipelineId: Pipeline\PipelineId::from(2),
            status: Pipeline\Status::WaitingForResource,
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectId->equals($object2->projectId)
            && $object1->ref->equals($object2->ref)
            && $object1->pipelineId->equals($object2->pipelineId)
            && $object1->status->equals($object2->status)
        ;
    }
}
