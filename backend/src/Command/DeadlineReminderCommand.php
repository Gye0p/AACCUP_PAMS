<?php

namespace App\Command;

use App\Entity\AccreditationCycle;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\AccreditationCycleRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:deadline-reminder',
    description: 'Create in-app deadline reminders for approaching and overdue accreditation cycles.',
)]
class DeadlineReminderCommand extends Command
{
    /** Days before deadline at which reminders are sent */
    private const REMINDER_DAYS = [30, 14, 7, 1];

    public function __construct(
        private AccreditationCycleRepository $cycleRepository,
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
        private NotificationService $notificationService,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $today = new \DateTimeImmutable('today');
        $sent  = 0;

        $cycles = $this->cycleRepository->findBy(['status' => AccreditationCycle::STATUS_ACTIVE]);

        foreach ($cycles as $cycle) {
            $deadline = $cycle->getComplianceDeadline();
            if (!$deadline) {
                continue;
            }

            $deadlineImm = \DateTimeImmutable::createFromInterface($deadline);
            $isOverdue   = $today > $deadlineImm;
            $diff        = (int) $today->diff($deadlineImm)->days;

            $recipients  = $this->getRecipients($cycle);
            $programName = $cycle->getProgram()->getName();
            $link        = '/cycles/' . $cycle->getId();

            if ($isOverdue) {
                // One overdue reminder per cycle (checked via link uniqueness)
                $dedupLink = $link . '_overdue';
                foreach ($recipients as $recipient) {
                    if (!$this->notificationRepository->reminderAlreadySent(
                        $recipient, Notification::TYPE_OVERDUE, $dedupLink
                    )) {
                        $this->notificationService->notify(
                            $recipient,
                            Notification::TYPE_OVERDUE,
                            sprintf('%s compliance deadline has passed. The cycle is now overdue.', $programName),
                            $dedupLink
                        );
                        $sent++;
                    }
                }
            } else {
                // Check each threshold (30 / 14 / 7 / 1)
                foreach (self::REMINDER_DAYS as $days) {
                    if ($diff === $days) {
                        $dedupLink = $link . '_d' . $days;
                        foreach ($recipients as $recipient) {
                            if (!$this->notificationRepository->reminderAlreadySent(
                                $recipient, Notification::TYPE_DEADLINE_REMINDER, $dedupLink
                            )) {
                                $this->notificationService->notify(
                                    $recipient,
                                    Notification::TYPE_DEADLINE_REMINDER,
                                    sprintf(
                                        '%s compliance deadline is in %d day%s.',
                                        $programName,
                                        $days,
                                        $days === 1 ? '' : 's'
                                    ),
                                    $dedupLink
                                );
                                $sent++;
                            }
                        }
                        break;
                    }
                }
            }
        }

        $this->em->flush();
        $io->success(sprintf('Done. Sent %d new deadline notification(s).', $sent));

        return Command::SUCCESS;
    }

    /** @return User[] */
    private function getRecipients(AccreditationCycle $cycle): array
    {
        $program = $cycle->getProgram();
        $college = $program->getCollege();

        return array_unique(array_merge(
            $this->userRepository->findBy(['program' => $program, 'role' => 'ROLE_PROGRAM_HEAD']),
            $this->userRepository->findBy(['college' => $college, 'role' => 'ROLE_AREA_COORDINATOR']),
            $this->userRepository->findBy(['role' => 'ROLE_QUAMC_ADMIN']),
        ), SORT_REGULAR);
    }
}
