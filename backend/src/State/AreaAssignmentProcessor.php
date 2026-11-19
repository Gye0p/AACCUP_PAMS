<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\AreaAssignment;
use App\Entity\AccreditationCycle;
use App\Entity\Notification;
use App\Repository\AreaCoordinatorAssignmentRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 30)]
class AreaAssignmentProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService,
        private AreaCoordinatorAssignmentRepository $acAssignmentRepository,
        private UserRepository $userRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $wasNotApproved = $data instanceof AreaAssignment && $data->getIaStatus() !== AreaAssignment::IA_STATUS_APPROVED;

        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if ($data instanceof AreaAssignment) {
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

                $program = $cycle->getProgram();
                if ($program !== null && $program->advanceAccreditationLevel()) {
                    $this->entityManager->persist($program);
                }
            }

            // If area just became approved, notify Area Coordinators and Program Head
            if ($wasNotApproved && $data->getIaStatus() === AreaAssignment::IA_STATUS_APPROVED) {
                $link = '/cycles/' . $cycle->getId();
                $message = sprintf('Area %s has been approved by the Internal Accreditor.', $data->getArea()->getAreaNumber());

                // Notify Area Coordinators
                $acAssignments = $this->acAssignmentRepository->findBy(['areaAssignment' => $data]);
                $acs = array_map(fn($a) => $a->getAreaCoordinator(), $acAssignments);
                $this->notificationService->notifyMany($acs, Notification::TYPE_AREA_APPROVED, $message, $link);

                // Notify Program Head
                $program = $cycle->getProgram();
                $programHeads = $this->userRepository->findBy(['program' => $program, 'role' => 'ROLE_PROGRAM_HEAD']);
                $this->notificationService->notifyMany($programHeads, Notification::TYPE_AREA_APPROVED, $message, $link);
            }

            $this->entityManager->flush();
        }

        return $result;
    }
}
