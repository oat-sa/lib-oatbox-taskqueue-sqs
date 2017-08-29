<?php

namespace oat\TaskSqsQueue\Action;

use oat\oatbox\extension\InstallAction;
use oat\TaskSqsQueue\Persistence\SqsPersistence;
use oat\TaskSqsQueue\SqsQueue;

class CreateQueue extends InstallAction
{
    public function __invoke($params)
    {
        var_dump($params);die;

        $queue = $this->getServiceManager()->get(Queue::SERVICE_ID);

        if (!$queue instanceof SqsQueue) {
            return \common_report_Report::createFailure('Queue needs to be an SqsQueue instance.');
        }

        $queueName = array_shift($params) ?: $queue->getOption(SqsQueue::OPTION_QUEUE_NAME);

        if (empty($queueName)) {
            return \common_report_Report::createFailure('Queue name can not be empty.');
        }

        /** @var SqsPersistence $persistance */
        $persistance = $queue->getPersistence();

        try {
            $result = $persistance->getSqsClient()->createQueue([
                'QueueName' => $queueName,
                'Attributes' => [
                    'DelaySeconds' => 5,
                    'MaximumMessageSize' => 4096, // 4 KB
                ]
            ]);

            var_dump($result);

            return \common_report_Report::createSuccess('Queue "'. $queueName .'" has been created.');
        } catch (AwsException $e) {
            return \common_report_Report::createFailure($e->getMessage());
        }
    }
}