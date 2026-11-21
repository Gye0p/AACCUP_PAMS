<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\AccreditationCycle;
use App\Entity\AreaAssignment;
use App\Entity\Program;
use App\Entity\AreaCoordinatorAssignment;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\AaccupAreaRepository;
use App\Repository\InternalAccreditorAssignmentRepository;
use App\Repository\UserRepository;
use App\Repository\AreaCoordinatorAssignmentRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 10)]
class AccreditationCycleProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private AaccupAreaRepository $areaRepository,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private NotificationService $notificationService,
        private AreaCoordinatorAssignmentRepository $acAssignmentRepository,
        private InternalAccreditorAssignmentRepository $iaAssignmentRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $isNew = $data instanceof AccreditationCycle && !$data->getId();

        if ($isNew && $data instanceof AccreditationCycle) {
            $program = $data->getProgram();
            $level = $program?->getAccreditationLevel();
            if (!in_array($level, Program::ACCREDITATION_LEVELS, true)) {
                throw new UnprocessableEntityHttpException('The selected program does not have a valid accreditation level.');
            }
            $data->setAccreditationLevel($level);
            if ($level === 'Level III') {
                if (!in_array($data->getProgramType(), ['undergraduate', 'graduate'], true)) {
                    throw new UnprocessableEntityHttpException('Select an undergraduate or graduate program type.');
                }
                if (count($data->getOptionalAreaIds() ?? []) !== 2) {
                    throw new UnprocessableEntityHttpException('Level III accreditation requires exactly two optional areas.');
                }
            }
        }

        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        // Auto-create 10 AreaAssignments after the cycle is persisted for the first time
        if ($isNew && $data instanceof AccreditationCycle) {
            $level = $data->getAccreditationLevel();
            $areaNumbers = [];

            if ($level === 'Level I' || $level === 'Level II') {
                $areaNumbers = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
            } elseif ($level === 'Level III') {
                $areaNumbers = array_merge(self::mandatoryAreaNumbers($data->getProgramType()), array_map(
                    static fn ($id) => (string) $id,
                    $data->getOptionalAreaIds() ?? []
                ));
            } elseif ($level === 'Level IV') {
                $areaNumbers = ['IV-1', 'IV-2', 'IV-3', 'IV-4', 'IV-5'];
            }

            $areas = [];
            foreach ($areaNumbers as $areaNumber) {
                $area = ctype_digit($areaNumber)
                    ? $this->areaRepository->find((int) $areaNumber)
                    : $this->areaRepository->findOneBy(['areaNumber' => $areaNumber]);
                if ($area) {
                    $areas[] = $area;
                }
            }

            foreach ($areas as $area) {
                $assignment = new AreaAssignment();
                $assignment->setCycle($data);
                $assignment->setArea($area);
                $this->entityManager->persist($assignment);
                $this->entityManager->flush();

                $areaCoordinators = $this->userRepository->findBy([
                    'college' => $data->getProgram()->getCollege(),
                    'role' => 'ROLE_AREA_COORDINATOR',
                ]);
                foreach ($areaCoordinators as $areaCoordinator) {
                    $coordinatorAssignment = new AreaCoordinatorAssignment();
                    $coordinatorAssignment->setAreaCoordinator($areaCoordinator);
                    $coordinatorAssignment->setAreaAssignment($assignment);
                    $this->entityManager->persist($coordinatorAssignment);
                }
            }
            $this->entityManager->flush();

            // Send notifications
            $this->sendCycleCreatedNotifications($data);
            $this->entityManager->flush();
        }

        return $result;
    }

    public static function mandatoryAreaNumbers(?string $programType): array
    {
        return $programType === 'graduate' ? ['III', 'V'] : ['III', 'VI'];
    }

    private function sendCycleCreatedNotifications(AccreditationCycle $cycle): void
    {
        $program = $cycle->getProgram();
        $college = $program->getCollege();
        $link = '/cycles/' . $cycle->getId();
        $message = sprintf(
            'A new accreditation cycle (%s) has been created for %s.',
            $cycle->getAcademicYear(),
            $program->getName()
        );

        // Notify Program Head
        $programHeads = $this->userRepository->findBy(['program' => $program, 'role' => 'ROLE_PROGRAM_HEAD']);
        $this->notificationService->notifyMany($programHeads, Notification::TYPE_CYCLE_CREATED, $message, $link);

        // Notify Deans of the college
        $deans = $this->userRepository->findBy(['college' => $college, 'role' => 'ROLE_DEAN']);
        $this->notificationService->notifyMany($deans, Notification::TYPE_CYCLE_CREATED, $message, $link);

        // Notify Area Coordinators of the college
        $areaCoordinators = $this->userRepository->findBy(['college' => $college, 'role' => 'ROLE_AREA_COORDINATOR']);
        $this->notificationService->notifyMany($areaCoordinators, Notification::TYPE_CYCLE_CREATED, $message, $link);
    }
}
