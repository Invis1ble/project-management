<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;

final readonly class TagFactory implements TagFactoryInterface
{
    public function __construct(private CommitFactoryInterface $commitFactory)
    {
    }

    public function createTag(
        string $name,
        string $commitId,
        ?string $commitMessage,
        string $commitCreatedAt,
        string $target,
        ?string $message,
        ?string $createdAt,
    ): Tag {
        return new Tag(
            commit: $this->commitFactory->createCommit(
                id: $commitId,
                message: $commitMessage,
                createdAt: $commitCreatedAt,
            ),
            name: Name::fromString($name),
            target: CommitId::fromString($target),
            message: null === $message ? null : Message::fromString($message),
            createdAt: null === $createdAt ? null : new \DateTimeImmutable($createdAt),
        );
    }
}
