<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Event;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;

abstract readonly class AbstractReleasePublicationEvent extends ReleasePublicationIdAwareEvent
{
    public function __construct(
        ReleasePublicationId $id,
        public Name $branchName,
    ) {
        parent::__construct($id);
    }
}
