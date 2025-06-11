<?php

namespace App\Twig;

use App\Service\MessageService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MessageExtension extends AbstractExtension
{
    public function __construct(
        private readonly MessageService $messageService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this, 'getUnreadMessagesCount']),
        ];
    }

    public function getUnreadMessagesCount(): int
    {
        return $this->messageService->getUnreadMessagesCount();
    }
} 