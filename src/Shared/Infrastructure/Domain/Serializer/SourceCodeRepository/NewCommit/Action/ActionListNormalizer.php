<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\SourceCodeRepository\NewCommit\Action;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionChmod;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionCreate;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionDelete;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionMove;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\Action\ActionUpdate;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;

final class ActionListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return ActionList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return match ($data['name']) {
            'chmod' => ActionChmod::class,
            'create' => ActionCreate::class,
            'delete' => ActionDelete::class,
            'move' => ActionMove::class,
            'update' => ActionUpdate::class,
        };
    }
}
