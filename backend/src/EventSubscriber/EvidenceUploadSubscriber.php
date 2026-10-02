<?php

namespace App\EventSubscriber;

use App\Entity\Evidence;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;

class EvidenceUploadSubscriber implements EventSubscriber
{
    public function __construct(private Security $security) {}

    public function getSubscribedEvents(): array
    {
        return [Events::prePersist];
    }

    public function __invoke(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof Evidence || $entity->getUploadedBy() !== null) {
            return;
        }

        $user = $this->security->getUser();
        if ($user !== null) {
            $entity->setUploadedBy($user);
        }
    }
}