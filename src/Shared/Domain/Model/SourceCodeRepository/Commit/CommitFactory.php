<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;

final readonly class CommitFactory implements CommitFactoryInterface
{
    public function createCommit(
        string $id,
        ?string $message,
        string $createdAt,
    ): Commit {
        return new Commit(
            id: CommitId::fromString($id),
            message: null === $message ? null : Message::fromString($message),
            createdAt: new \DateTimeImmutable($createdAt),
        );
    }
}
