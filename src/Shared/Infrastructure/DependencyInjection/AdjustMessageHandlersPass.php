<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\DependencyInjection;

use Invis1ble\Messenger\Command\CommandHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdjustMessageHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $messageHandlerTag = 'messenger.message_handler';

        $container->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->setPublic(true)
            ->clearTag($messageHandlerTag)
            ->addTag($messageHandlerTag, [
                'bus' => 'messenger.bus.command.async',
            ])
        ;
    }
}
