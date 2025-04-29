<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SourceCodeRepository\Branch;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch\BranchCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<BranchCreated>
 */
class BranchCreatedSerializationTest extends SerializationTestCase
{
    protected function createObject(): BranchCreated
    {
        return new BranchCreated(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            name: Branch\Name::fromString('feature/test'),
            protected: false,
            guiUrl: new Uri('https://example.com/branch/feature/test'),
            commitId: Commit\CommitId::fromString('87654321fedcba0987654321fecdba0987654321'),
            commitMessage: Commit\Message::fromString('Init new branch'),
            commitCreatedAt: new \DateTimeImmutable(),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectId->equals($object2->projectId)
            && $object1->ref->equals($object2->ref)
            && $object1->name->equals($object2->name)
            && $object1->protected === $object2->protected
            && (string) $object1->guiUrl === (string) $object2->guiUrl
            && $object1->commitId->equals($object2->commitId)
            && $object1->commitMessage->equals($object2->commitMessage)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->commitCreatedAt == $object2->commitCreatedAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
