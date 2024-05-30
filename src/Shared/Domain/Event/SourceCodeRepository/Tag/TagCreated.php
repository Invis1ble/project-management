<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag;

use ProjectManagement\Shared\Domain\Event\SourceCodeRepository\ProjectIdAwareEvent;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Name;

final readonly class TagCreated extends ProjectIdAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        public Name $tagName,
        public Ref $ref,
        public ?Message $message,
        public ?\DateTimeImmutable $createdAt,
    ) {
        parent::__construct($projectId);
    }
}
