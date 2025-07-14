<?php

declare(strict_types=1);

namespace Phosagro\Voting;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\Manager\VotingManager;
use Phosagro\Site\SiteInfo;
use Phosagro\Subscription\RubricCode;
use Phosagro\Subscription\RubricMap;
use Phosagro\System\AgentInterface;
use Phosagro\System\UrlManager;

final class SubscriptionPostingBuilder implements AgentInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly RubricMap $rubrics,
        private readonly SiteInfo $site,
        private readonly UrlManager $urls,
        private readonly VotingManager $votings,
    ) {}

    public function execute(): void
    {
        $manager = new \CPosting();

        $rubric = $this->rubrics->getKnownRubric(RubricCode::VOTINGS);

        if (!$rubric->rubricActive) {
            return;
        }

        foreach ($this->votings->getVotingsWaitingForPositng() as $voting) {
            $url = $this->urls->makeAbsolute(sprintf('/profile/voting/%d', $voting->votingIdentifier));

            $variables = [
                '#SITE_NAME#' => $this->site->getSiteName(),
                '#VOTING_NAME#' => $voting->votingName,
                '#VOTING_URL#' => $url,
            ];

            $addResult = $manager->Add([
                'BCC_FIELD' => '',
                'BODY' => GetMessage('VOTING_STARTED_EMAIL_BODY', $variables),
                'BODY_TYPE' => 'text',
                'CHARSET' => 'UTF-8',
                'DATE_SENT' => '',
                'DIRECT_SEND' => 'Y',
                'EMAIL_FILTER' => '',
                'FROM_FIELD' => $this->site->getAdminEmail(),
                'GROUP_ID' => [],
                'MSG_CHARSET' => 'UTF-8',
                'RUB_ID' => [$rubric->rubricIdentifier],
                'STATUS' => 'D',
                'SUBJECT' => GetMessage('VOTING_STARTED_EMAIL_SUBJECT', $variables),
                'SUBSCR_FORMAT' => '',
                'TO_FIELD' => '',
            ]);

            if (!$addResult) {
                $this->logger->log(
                    LogEvent::SUBSCRIPTION_SENDING_FAILED,
                    sprintf('%d', $voting->votingIdentifier),
                    sprintf('Can not add posting for voting "%s". %s', $voting->votingIdentifier, $manager->LAST_ERROR),
                );

                break;
            }

            $postingIdentifier = (int) $addResult;

            $this->votings->linkPostingToVoting($postingIdentifier, $voting->votingIdentifier);

            $statusChanged = $manager->ChangeStatus($postingIdentifier, 'P');

            if (!$statusChanged) {
                $this->logger->log(
                    LogEvent::SUBSCRIPTION_SENDING_FAILED,
                    sprintf('%d', $voting->votingIdentifier),
                    sprintf('Can not set status P for posting "%d". %s', $postingIdentifier, $manager->LAST_ERROR),
                );

                break;
            }

            $agent = sprintf('%s::AutoSend(%d, true);', \CPosting::class, $postingIdentifier);

            \CAgent::AddAgent($agent, 'subscribe', 'N', 60);
        }
    }
}
