<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Create and persist a notification for a single recipient.
     */
    public function notify(User $recipient, string $type, string $message, ?string $link = null): void
    {
        $notification = new Notification();
        $notification->setRecipient($recipient);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setLink($link);

        $this->em->persist($notification);
        // Caller must flush
    }

    /**
     * Create notifications for multiple recipients at once.
     * @param User[] $recipients
     */
    public function notifyMany(array $recipients, string $type, string $message, ?string $link = null): void
    {
        foreach ($recipients as $recipient) {
            $this->notify($recipient, $type, $message, $link);
        }
    }
}
