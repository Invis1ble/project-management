<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\DevelopmentCollaboration\MergeRequest;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<MergeRequestMerged>
 */
class MergeRequestMergedSerializationTest extends SerializationTestCase
{
    protected function createObject(): MergeRequestMerged
    {
        return new MergeRequestMerged(
            projectId: Project\ProjectId::from(1),
            projectName: Project\Name::fromString('my-group/my-project'),
            mergeRequestIid: MergeRequest\MergeRequestIid::from(2),
            title: MergeRequest\Title::fromString('Fix bug'),
            sourceBranchName: Branch\Name::fromString('TEST-1'),
            targetBranchName: Branch\Name::fromString('develop'),
            status: MergeRequest\Status::Open,
            guiUrl: new Uri('https://gitlab.example.com/my-group/my-project/merge_requests/2'),
            details: new MergeRequest\Details\Details(
                status: new MergeRequest\Details\Status\StatusChecking(),
            ),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectId->equals($object2->projectId)
            && $object1->mergeRequestIid->equals($object2->mergeRequestIid)
            && $object1->title->equals($object2->title)
            && $object1->sourceBranchName->equals($object2->sourceBranchName)
            && $object1->targetBranchName->equals($object2->targetBranchName)
            && $object1->details->equals($object2->details)
        ;
    }
}
