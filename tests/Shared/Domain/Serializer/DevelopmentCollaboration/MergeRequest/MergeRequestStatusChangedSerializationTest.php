<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\DevelopmentCollaboration\MergeRequest;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\ProjectManagement\Shared\Domain\Event\DevelopmentCollaboration\MergeRequest\MergeRequestStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<MergeRequestStatusChanged>
 */
class MergeRequestStatusChangedSerializationTest extends SerializationTestCase
{
    protected function createObject(): MergeRequestStatusChanged
    {
        return new MergeRequestStatusChanged(
            projectId: Project\ProjectId::from(1),
            projectName: Project\Name::fromString('my-group/my-project'),
            mergeRequestIid: MergeRequest\MergeRequestIid::from(2),
            title: MergeRequest\Title::fromString('Fix bug'),
            sourceBranchName: Branch\Name::fromString('TEST-1'),
            targetBranchName: Branch\Name::fromString('develop'),
            previousStatus: MergeRequest\Status::Declined,
            status: MergeRequest\Status::Open,
            guiUrl: new Uri('https://gitlab.example.com/my-group/my-project/merge_requests/2'),
            previousDetails: new MergeRequest\Details\Details(
                status: new MergeRequest\Details\Status\StatusChecking(),
            ),
            details: new MergeRequest\Details\Details(
                status: new MergeRequest\Details\Status\StatusMergeable(),
            ),
            tickInterval: new \DateInterval('PT10S'),
            maxAwaitingTime: new \DateInterval('PT1M'),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        if ($object1->projectId->equals($object2->projectId)
            && $object1->projectName->equals($object2->projectName)
            && $object1->mergeRequestIid->equals($object2->mergeRequestIid)
            && $object1->title->equals($object2->title)
            && $object1->sourceBranchName->equals($object2->sourceBranchName)
            && $object1->targetBranchName->equals($object2->targetBranchName)
            && $object1->status->equals($object2->status)
            && $object1->previousStatus->equals($object2->previousStatus)
            && (string) $object1->guiUrl === (string) $object2->guiUrl
            && $object1->previousDetails->equals($object2->previousDetails)
            && $object1->details->equals($object2->details)
        ) {
            return true;
        }

        $datetime = new \DateTimeImmutable();

        return
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            $datetime->add($object1->tickInterval) == $datetime->add($object2->tickInterval)
            && $datetime->add($object1->maxAwaitingTime) == $datetime->add($object2->maxAwaitingTime)
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
