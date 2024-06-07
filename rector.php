<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
    ])
    ->withSkip([
        __DIR__ . '/public/index.php',

        AddOverrideAttributeToOverriddenMethodsRector::class,

        /**
         * Disable due to bug with Reflection
         *
         * @see Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\AbstractStatus::setPublicationProperty()
         * @see Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublication::__construct(status)
         */
        ReadOnlyPropertyRector::class,
    ])
    ->withPhpSets()
    ->withSets([
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,

        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ])
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withCache(
        cacheDirectory: '/tmp/rector',
        cacheClass: FileCacheStorage::class,
    );
