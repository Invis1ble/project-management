<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\ProjectIdAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Name;

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
