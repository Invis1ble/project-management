<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SourceCodeRepository\Commit;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Commit\CommitCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<CommitCreated>
 */
class CommitCreatedSerializationTest extends SerializationTestCase
{
    protected function createObject(): CommitCreated
    {
        return new CommitCreated(
            projectId: Project\ProjectId::from(1),
            branchName: Branch\Name::fromString('feature/test'),
            startBranchName: Branch\Name::fromString('develop'),
            commitId: Commit\CommitId::fromString('1234567890abcdef1234567890abcdef12345678'),
            message: Commit\Message::fromString('Init new branch'),
            guiUrl: new Uri('https://example.com/commit/1234567890abcdef1234567890abcdef12345678'),
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectId->equals($object2->projectId)
            && $object1->branchName->equals($object2->branchName)
            && $object1->startBranchName->equals($object2->startBranchName)
            && $object1->commitId->equals($object2->commitId)
            && $object1->message->equals($object2->message)
            && (string) $object1->guiUrl === (string) $object2->guiUrl
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->createdAt == $object2->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
