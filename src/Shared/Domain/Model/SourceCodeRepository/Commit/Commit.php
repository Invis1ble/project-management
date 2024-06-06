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

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id)
            && $this->message?->equals($other->message)
            && $this->createdAt === $other->createdAt
        ;
    }
}
