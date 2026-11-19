<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function countUnread(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.recipient = :user')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Check whether a deadline reminder of given type was already sent for this cycle */
    public function reminderAlreadySent(User $user, string $type, string $linkContains): bool
    {
        $count = (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.recipient = :user')
            ->andWhere('n.type = :type')
            ->andWhere('n.link LIKE :link')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('link', '%' . $linkContains . '%')
            ->getQuery()
            ->getSingleScalarResult();
        return $count > 0;
    }
}
