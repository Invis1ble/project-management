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
        if (null === $this->message) {
            if (null !== $other->message) {
                return false;
            }
        } elseif (null === $other->message) {
            return false;
        } elseif (!$this->message->equals($other->message)) {
            return false;
        }

        return $this->id->equals($other->id)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $this->createdAt == $other->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
