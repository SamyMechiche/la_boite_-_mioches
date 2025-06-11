<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/message')]
#[IsGranted('ROLE_USER')]
final class MessageController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageRepository $messageRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('', name: 'app_message')]
    public function index(): Response
    {
        $user = $this->getUser();
        $messages = $this->messageRepository->findByUser($user);

        // Get potential recipients based on user role
        $recipients = $this->getPotentialRecipients($user);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
            'recipients' => $recipients,
        ]);
    }

    #[Route('/send', name: 'app_message_send', methods: ['POST'])]
    public function send(Request $request): Response
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['recipientId']) || !isset($data['content'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $recipient = $this->userRepository->find($data['recipientId']);
        if (!$recipient) {
            return $this->json(['error' => 'Recipient not found'], Response::HTTP_NOT_FOUND);
        }

        // Validate if user can send message to recipient
        if (!$this->canSendMessageTo($user, $recipient)) {
            return $this->json(['error' => 'You cannot send messages to this user'], Response::HTTP_FORBIDDEN);
        }

        $message = new Message();
        $message->setSender($user);
        $message->setReciever($recipient);
        $message->setContent($data['content']);
        $message->setSentAt(new \DateTimeImmutable());
        $message->setIsRead(false);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
                'sender' => [
                    'id' => $user->getId(),
                    'name' => $user->getFname() . ' ' . $user->getLname(),
                ],
            ],
        ]);
    }

    #[Route('/{id}/read', name: 'app_message_read', methods: ['POST'])]
    public function markAsRead(Message $message): Response
    {
        $user = $this->getUser();
        
        // Check if user is the receiver
        if ($message->getReciever() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $message->setIsRead(true);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/reply', name: 'app_message_reply', methods: ['POST'])]
    public function reply(Request $request, Message $message): Response
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return $this->json(['error' => 'Message content is required'], Response::HTTP_BAD_REQUEST);
        }

        // Check if user is either sender or receiver of the original message
        if ($message->getSender() !== $user && $message->getReciever() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Create reply message
        $reply = new Message();
        $reply->setSender($user);
        $reply->setReciever($message->getSender() === $user ? $message->getReciever() : $message->getSender());
        $reply->setContent($data['content']);
        $reply->setSentAt(new \DateTimeImmutable());
        $reply->setIsRead(false);

        $this->entityManager->persist($reply);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $reply->getId(),
                'content' => $reply->getContent(),
                'sentAt' => $reply->getSentAt()->format('Y-m-d H:i:s'),
                'sender' => [
                    'id' => $user->getId(),
                    'name' => $user->getFname() . ' ' . $user->getLname(),
                ],
            ],
        ]);
    }

    #[Route('/delete', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!isset($data['messageIds']) || !is_array($data['messageIds'])) {
            return $this->json(['error' => 'No messages selected'], Response::HTTP_BAD_REQUEST);
        }

        $messages = $this->messageRepository->findBy(['id' => $data['messageIds']]);
        $deletedCount = 0;

        foreach ($messages as $message) {
            // Only allow deletion if user is either sender or receiver
            if ($message->getSender() === $user || $message->getReciever() === $user) {
                $this->entityManager->remove($message);
                $deletedCount++;
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'deletedCount' => $deletedCount
        ]);
    }

    private function getPotentialRecipients(User $user): array
    {
        $recipients = [];
        
        // If user is a parent, they can message educators
        if (in_array('ROLE_PARENT', $user->getRoles())) {
            $educators = $this->userRepository->findByRole('ROLE_EDUCATOR');
            foreach ($educators as $educator) {
                $recipients[] = [
                    'id' => $educator->getId(),
                    'name' => $educator->getFname() . ' ' . $educator->getLname(),
                    'role' => 'Educator'
                ];
            }
        }
        
        // If user is an educator, they can message parents and admins
        if (in_array('ROLE_EDUCATOR', $user->getRoles())) {
            $parents = $this->userRepository->findByRole('ROLE_PARENT');
            foreach ($parents as $parent) {
                $recipients[] = [
                    'id' => $parent->getId(),
                    'name' => $parent->getFname() . ' ' . $parent->getLname(),
                    'role' => 'Parent'
                ];
            }
            
            $admins = $this->userRepository->findByRole('ROLE_ADMIN');
            foreach ($admins as $admin) {
                $recipients[] = [
                    'id' => $admin->getId(),
                    'name' => $admin->getFname() . ' ' . $admin->getLname(),
                    'role' => 'Administrator'
                ];
            }
        }

        // If user is an admin, they can message everyone
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Get all educators
            $educators = $this->userRepository->findByRole('ROLE_EDUCATOR');
            foreach ($educators as $educator) {
                $recipients[] = [
                    'id' => $educator->getId(),
                    'name' => $educator->getFname() . ' ' . $educator->getLname(),
                    'role' => 'Educator'
                ];
            }

            // Get all parents
            $parents = $this->userRepository->findByRole('ROLE_PARENT');
            foreach ($parents as $parent) {
                $recipients[] = [
                    'id' => $parent->getId(),
                    'name' => $parent->getFname() . ' ' . $parent->getLname(),
                    'role' => 'Parent'
                ];
            }
        }

        return $recipients;
    }

    private function canSendMessageTo(User $sender, User $recipient): bool
    {
        $senderRoles = $sender->getRoles();
        $recipientRoles = $recipient->getRoles();

        // Admins can message everyone
        if (in_array('ROLE_ADMIN', $senderRoles)) {
            return true;
        }

        // Parents can message educators
        if (in_array('ROLE_PARENT', $senderRoles) && in_array('ROLE_EDUCATOR', $recipientRoles)) {
            return true;
        }

        // Educators can message parents and admins
        if (in_array('ROLE_EDUCATOR', $senderRoles) && 
            (in_array('ROLE_PARENT', $recipientRoles) || in_array('ROLE_ADMIN', $recipientRoles))) {
            return true;
        }

        return false;
    }
}
