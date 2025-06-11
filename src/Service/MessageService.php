<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;

class MessageService
{
    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly Security $security
    ) {
    }

    public function getUnreadMessagesCount(): int
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return 0;
        }

        return $this->messageRepository->findUnreadCount($user);
    }
} 