<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Exception\HotfixPublicationNotFoundException;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

interface HotfixPublicationRepositoryInterface
{
    public function contains(HotfixPublicationId $id): bool;

    /**
     * @throws HotfixPublicationNotFoundException
     */
    public function get(HotfixPublicationId $id): HotfixPublicationInterface;

    /**
     * @throws HotfixPublicationNotFoundException
     */
    public function getLatest(): HotfixPublicationInterface;

    /**
     * @throws HotfixPublicationNotFoundException
     */
    public function getLatestByTagName(Tag\VersionName $tagName): HotfixPublicationInterface;

    public function store(HotfixPublicationInterface $publication): void;
}
