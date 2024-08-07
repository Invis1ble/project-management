<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetLatestHotfixPublicationByTag;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

final readonly class GetLatestHotfixPublicationByTagQuery implements QueryInterface
{
    public function __construct(public Tag\VersionName $tagName)
    {
    }
}
