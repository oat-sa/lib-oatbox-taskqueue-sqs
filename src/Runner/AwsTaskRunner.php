<?php

namespace oat\TaskSqsQueue\Runner;

use oat\oatbox\action\ActionService;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\oatbox\task\TaskInterface\TaskRunner as TaskRunnerInterface;
use oat\TaskSqsQueue\AwsTask;
use oat\TaskSqsQueue\SqsQueue;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class AwsTaskRunner implements TaskRunnerInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @param AwsTask $task
     * @return \common_report_Report
     */
    public function run(Task $task)
    {
        if ($task->hasBeenReceived()) {
            $message = 'Task '. $task->getId() .' ignored. It has been received.';
            \common_report_Report::createInfo($message);
            \common_Logger::i($message);
            return;
        }

        \common_Logger::d('Running task '. $task->getId());
        $report = \common_report_Report::createInfo(__('Running task %s', $task->getId()));

        /** @var SqsQueue $queue */
        $queue = $this->getServiceLocator()->get(Queue::SERVICE_ID);

        try {
            $invocable = $task->getInvocable();
            if (is_string($invocable)) {
                $invocable = $this->getServiceLocator()->get(ActionService::SERVICE_ID)
                    ->resolve($invocable);
            } else if ($invocable instanceof ServiceLocatorAwareInterface) {
                $invocable->setServiceLocator($this->getServiceLocator());
            }

            if (is_callable($invocable)) {
                $subReport = call_user_func($invocable, $task->getParameters());
                $report->add($subReport);
            } else {
                \common_Logger::e('"'. $invocable .'" is not callable.');
            }
        } catch (\Exception $e) {
            $message = 'Task ' . $task->getId() . ' failed. Error message: ' . $e->getMessage();
            \common_Logger::e($message);
            $report = \common_report_Report::createFailure($message);
        }

        $queue->getPersistence()->deleteMessage($task);

        return $report;
    }
}