<?php

namespace App\Notifications;

use App\Entity\MessageHistory;
use App\Repository\MessageHistoryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotificationHandler
{
    public function __construct(
        private readonly MessageHistoryRepository $messageHistoryRepository,
        private readonly LoggerInterface $logger,
        private readonly NotifierInterface $notifier
    ) {
    }

    public function __invoke(Notification $message)
    {
        $lastMessage = $this->messageHistoryRepository->getBySender($message->getSource());

        if ($lastMessage === null) {
            $this->logger->debug('Notify from ' . $message->getSource());
            $this->notifier->notify($message->getMessage());
            $this->saveMessageHistory($message);
        }
    }

    private function saveMessageHistory(Notification $message): void
    {
        $messageHistory = new MessageHistory();
        $messageHistory->setSender($message->getSource());
        $messageHistory->setSentDate(new \DateTimeImmutable());
        $this->messageHistoryRepository->save($messageHistory, true);
    }
}
