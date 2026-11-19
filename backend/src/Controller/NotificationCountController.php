<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications/unread-count', name: 'api_notifications_unread_count', methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class NotificationCountController extends AbstractController
{
    public function __construct(private NotificationRepository $notificationRepository) {}

    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['count' => 0]);
        }
        $count = $this->notificationRepository->countUnread($user);
        return $this->json(['count' => $count]);
    }
}
