<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model;

use ProjectManagement\Shared\Domain\Model\AbstractUuid;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Symfony\Component\Uid\Uuid;

final readonly class HotfixPublicationId extends AbstractUuid
{
    public static function generate(IssueList $hotfixes): self
    {
        if ($hotfixes->empty()) {
            throw new \InvalidArgumentException('No hotfixes provided');
        }

        $hotfixIds = iterator_to_array($hotfixes->map(fn (Issue $hotfix): string => (string) $hotfix->id));
        sort($hotfixIds);

        return new self(Uuid::v5(
            namespace: Uuid::fromString(Uuid::NAMESPACE_OID),
            name: implode(',', $hotfixIds),
        ));
    }
}
