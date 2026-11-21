<?php

namespace App\EventSubscriber;

use App\Entity\AuditLog;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;

class AuditLogSubscriber implements EventSubscriber
{
    private array $pending = [];

    public function __construct(
        private \Doctrine\ORM\EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function getSubscribedEvents(): array
    {
        return [Events::onFlush, Events::postFlush];
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $actor = $this->security->getUser();
        if ($actor === null) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->queue($entity, 'create', $unitOfWork->getEntityChangeSet($entity));
        }
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->queue($entity, 'update', $unitOfWork->getEntityChangeSet($entity));
        }
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $this->queue($entity, 'delete', []);
        }
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        if ($this->pending === []) {
            return;
        }

        $pending = $this->pending;
        $this->pending = [];
        foreach ($pending as $entry) {
            $audit = new AuditLog();
            $audit->setActor($this->security->getUser());
            $audit->setAction($entry['action']);
            $audit->setEntityType($entry['type']);
            $audit->setEntityId($this->entityId($entry['entity']));
            $audit->setChanges($entry['changes']);
            $this->entityManager->persist($audit);
        }
        $this->entityManager->flush();
    }

    private function queue(object $entity, string $action, array $changeSet): void
    {
        if ($entity instanceof AuditLog) {
            return;
        }

        $this->pending[] = [
            'entity' => $entity,
            'action' => $action,
            'type' => $entity::class,
            'changes' => $this->normalizeChanges($changeSet),
        ];
    }

    private function normalizeChanges(array $changeSet): array
    {
        $changes = [];
        foreach ($changeSet as $field => $values) {
            if (in_array($field, ['password', 'plainPassword'], true)) {
                $changes[$field] = '[REDACTED]';
                continue;
            }

            $changes[$field] = array_map(fn ($value) => $this->normalizeValue($value), $values);
        }
        return $changes;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }
        if (is_object($value)) {
            $id = method_exists($value, 'getId') ? $value->getId() : null;
            return ['type' => $value::class, 'id' => $id];
        }
        if (is_scalar($value) || $value === null) {
            return $value;
        }
        return (string) $value;
    }

    private function entityId(object $entity): ?string
    {
        if (!method_exists($entity, 'getId') || $entity->getId() === null) {
            return null;
        }
        return (string) $entity->getId();
    }
}
