<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\AccreditationCycle;
use App\Entity\AreaAssignment;
use App\Repository\AaccupAreaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 10)]
class AccreditationCycleProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private AaccupAreaRepository $areaRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $isNew = $data instanceof AccreditationCycle && !$data->getId();

        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        // Auto-create 10 AreaAssignments after the cycle is persisted for the first time
        if ($isNew && $data instanceof AccreditationCycle) {
            $areas = $this->areaRepository->findAll();
            foreach ($areas as $area) {
                $assignment = new AreaAssignment();
                $assignment->setCycle($data);
                $assignment->setArea($area);
                $this->entityManager->persist($assignment);
            }
            $this->entityManager->flush();
        }

        return $result;
    }
}
