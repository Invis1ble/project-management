<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\Http;

use Invis1ble\Messenger\Event\EventBusInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Branch\BranchCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit;

class PageController extends AbstractController
{
    #[Route(
        '/page',
        name: 'page',
        methods: ['GET'],
    )]
    public function publish(
        EventBusInterface $eventBus,
        UriFactoryInterface $uriFactory,
    ): Response {
        $eventBus->dispatch(new BranchCreated(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            name: Branch\Name::fromString('feature/test'),
            protected: false,
            guiUrl: $uriFactory->createUri('https://example.com/branch/feature/test'),
            commitId: Commit\CommitId::fromString('87654321fedcba0987654321fecdba0987654321'),
            commitMessage: Commit\Message::fromString('Init new branch'),
            commitCreatedAt: new \DateTimeImmutable(),
        ));

        return $this->render('publish_event.html.twig');
    }
}
