<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\Message;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types\AbstractStringableType;

final class MessageType extends AbstractStringableType
{
    public const string NAME = 'tag_message';

    public const string PHP_TYPE_FQCN = Message::class;
}
