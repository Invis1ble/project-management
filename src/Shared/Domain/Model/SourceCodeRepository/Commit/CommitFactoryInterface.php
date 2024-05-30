<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;

interface CommitFactoryInterface
{
    public function createCommit(
        string $id,
        ?string $message,
        string $createdAt,
    ): Commit;
}
