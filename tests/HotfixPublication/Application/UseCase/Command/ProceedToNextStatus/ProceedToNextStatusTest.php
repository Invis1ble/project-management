<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<ProceedToNextStatusCommand>
 */
class ProceedToNextStatusTest extends SerializationTestCase
{
    protected function createObject(): ProceedToNextStatusCommand
    {
        return new ProceedToNextStatusCommand(
            id: HotfixPublicationId::fromVersionName(Tag\VersionName::create()),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->id->equals($object2->id);
    }
}
