<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\Dictionary;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusBlockedStatus;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusChecking;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusCiMustPass;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusCiStillRunning;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusConflict;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusDiscussionsNotResolved;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusDraftStatus;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusExternalStatusChecks;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusFactoryInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusJiraAssociationMissing;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusMergeable;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNeedRebase;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNotApproved;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusNotOpen;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusRequestedChanges;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status\StatusUnchecked;

final readonly class StatusFactory implements StatusFactoryInterface
{
    public function createStatus(string $status): StatusInterface
    {
        return match ($status) {
            Dictionary::BlockedStatus->value => new StatusBlockedStatus(),
            Dictionary::Checking->value => new StatusChecking(),
            Dictionary::Unchecked->value => new StatusUnchecked(),
            Dictionary::CiMustPass->value => new StatusCiMustPass(),
            Dictionary::CiStillRunning->value => new StatusCiStillRunning(),
            Dictionary::DiscussionsNotResolved->value => new StatusDiscussionsNotResolved(),
            Dictionary::DraftStatus->value => new StatusDraftStatus(),
            Dictionary::ExternalStatusChecks->value => new StatusExternalStatusChecks(),
            Dictionary::Mergeable->value => new StatusMergeable(),
            Dictionary::NotApproved->value => new StatusNotApproved(),
            Dictionary::NotOpen->value => new StatusNotOpen(),
            Dictionary::JiraAssociationMissing->value => new StatusJiraAssociationMissing(),
            Dictionary::NeedRebase->value => new StatusNeedRebase(),
            Dictionary::Conflict->value => new StatusConflict(),
            Dictionary::RequestedChanges->value => new StatusRequestedChanges(),
        };
    }
}
