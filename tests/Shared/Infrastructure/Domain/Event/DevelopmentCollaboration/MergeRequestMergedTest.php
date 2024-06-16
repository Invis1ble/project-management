<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Event\DevelopmentCollaboration;

use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusChecking;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Title;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<MergeRequestMerged>
 */
class MergeRequestMergedTest extends SerializationTestCase
{
    protected function createObject(): MergeRequestMerged
    {
        return new MergeRequestMerged(
            projectId: ProjectId::from(1),
            mergeRequestId: MergeRequestId::from(2),
            title: Title::fromString('Fix bug'),
            sourceBranchName: Name::fromString('SOMEPROJECT-1'),
            targetBranchName: Name::fromString('develop'),
            details: new Details(
                status: new StatusChecking(),
            ),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectId->equals($object2->projectId)
            && $object1->mergeRequestId->equals($object2->mergeRequestId)
            && $object1->title->equals($object2->title)
            && $object1->sourceBranchName->equals($object2->sourceBranchName)
            && $object1->targetBranchName->equals($object2->targetBranchName)
            && $object1->details->equals($object2->details)
        ;
    }
}
