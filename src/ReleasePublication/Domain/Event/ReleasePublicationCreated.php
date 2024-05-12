<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;

final readonly class ReleasePublicationCreated extends ReleasePublicationIdAwareEvent
{
    public function __construct(
        ReleasePublicationId $id,
        public Name $branchName,
        public StatusInterface $status,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id);
    }
}
