<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;

final readonly class Commit
{
    public function __construct(
        public CommitId $id,
        public ?Message $message,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
