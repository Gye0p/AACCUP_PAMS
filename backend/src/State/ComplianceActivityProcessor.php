<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ComplianceActivity;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor', priority: 20)]
class ComplianceActivityProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private WorkflowInterface $complianceActivityStateMachine
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ComplianceActivity) {
            // Enforce deadline rule
            $cycle = $data->getAreaAssignment()->getCycle();
            $deadline = $cycle->getComplianceDeadline();
            
            // Allow end date to be exactly on the deadline
            if ($deadline) {
                // Set time to end of day for deadline comparison
                $deadlineComparison = (clone $deadline)->setTime(23, 59, 59);
                if ($data->getEndDate() > $deadlineComparison) {
                    throw new UnprocessableEntityHttpException('Activity end date cannot exceed the cycle compliance deadline.');
                }
            }

            // Handle Workflow Transitions
            if ($transition = $data->getTransition()) {
                if ($this->complianceActivityStateMachine->can($data, $transition)) {
                    $this->complianceActivityStateMachine->apply($data, $transition);
                } else {
                    throw new UnprocessableEntityHttpException(sprintf('Cannot apply transition "%s" from state "%s".', $transition, $data->getState()));
                }
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
