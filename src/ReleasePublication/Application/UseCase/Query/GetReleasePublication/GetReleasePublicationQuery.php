<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetReleasePublication;

use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;

final readonly class GetReleasePublicationQuery implements QueryInterface
{
    public function __construct(public ReleasePublicationId $publicationId)
    {
    }
}
