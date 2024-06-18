<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;

abstract class SerializerAwareTestCase extends KernelTestCase
{
    protected static function getSerializer(): Serializer
    {
        $container = static::getContainer();

        return $container->get('serializer');
    }
}
