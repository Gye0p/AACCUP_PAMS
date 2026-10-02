<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\AreaAssignment;
use App\Entity\AccreditationCycle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 30)]
class AreaAssignmentProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if ($data instanceof AreaAssignment) {
            // Check if all area assignments for this cycle are approved
            $cycle = $data->getCycle();
            $allApproved = true;

            foreach ($cycle->getAreaAssignments() as $assignment) {
                if ($assignment->getIaStatus() !== AreaAssignment::IA_STATUS_APPROVED) {
                    $allApproved = false;
                    break;
                }
            }

            if ($allApproved && $cycle->getStatus() !== AccreditationCycle::STATUS_COMPLETED) {
                $cycle->setStatus(AccreditationCycle::STATUS_COMPLETED);
                $this->entityManager->persist($cycle);
                $this->entityManager->flush();
            }
        }

        return $result;
    }
}
