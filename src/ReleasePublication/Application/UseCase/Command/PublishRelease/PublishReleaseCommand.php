<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\PublishRelease;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationIdAwareCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

final readonly class PublishReleaseCommand extends ReleasePublicationIdAwareCommand
{
    public function __construct(
        ReleasePublicationId $id,
        public Tag\VersionName $tagName,
        public Tag\Message $tagMessage,
    ) {
        parent::__construct($id);
    }
}
