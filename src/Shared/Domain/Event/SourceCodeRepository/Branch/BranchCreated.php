<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriInterface;

final readonly class BranchCreated extends RefAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Ref $ref,
        public Branch\Name $name,
        public bool $protected,
        public UriInterface $guiUrl,
        public Commit\CommitId $commitId,
        public ?Commit\Message $commitMessage,
        public \DateTimeImmutable $commitCreatedAt,
    ) {
        parent::__construct($projectId, $ref);
    }
}
