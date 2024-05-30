<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model;

use ProjectManagement\Shared\Domain\Model\AbstractUuid;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;
use Symfony\Component\Uid\Uuid;

final readonly class HotfixPublicationId extends AbstractUuid
{
    public static function generate(VersionName $tagName): self
    {
        return new self(Uuid::v5(
            namespace: Uuid::fromString(Uuid::NAMESPACE_OID),
            name: (string) $tagName,
        ));
    }
}
