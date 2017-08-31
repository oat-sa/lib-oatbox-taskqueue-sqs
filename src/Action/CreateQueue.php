<?php

namespace oat\TaskSqsQueue\Action;

use Aws\Exception\AwsException;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\task\Queue;
use oat\TaskSqsQueue\Persistence\SqsPersistence;
use oat\TaskSqsQueue\SqsQueue;

/**
 * Creates the given SQS Queue. It can be run only after the SqsQueue service has been initialized.
 *
 * Queue name can be passed as a CLI parameter otherwise it will be fetched from the SqsQueue service.
 *
 * @author <gyula@taotesting.com>
 */
class CreateQueue extends InstallAction
{
    public function __invoke($params)
    {
        $queue = $this->getServiceManager()->get(Queue::SERVICE_ID);

        if (!$queue instanceof SqsQueue) {
            return \common_report_Report::createFailure('Queue service needs to be an SqsQueue instance.');
        }

        $queueName = array_shift($params) ?: $queue->getOption(SqsQueue::OPTION_QUEUE_NAME);

        if (empty($queueName)) {
            return \common_report_Report::createFailure('Queue name can not be empty.');
        }

        /** @var SqsPersistence $persistance */
        $persistance = $queue->getPersistence();

        try {
            // Note: we are creating a Standard Queue for the time being. More development needed to customize this for creating FIFO Queue.
            /** @see http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#createqueue */
            $result = $persistance->getSqsClient()->createQueue([
                'QueueName' => $queueName,
                'Attributes' => [
                    'DelaySeconds' => 0,
                    'VisibilityTimeout' => 600
                ]
            ]);

            if ($result->hasKey('QueueUrl')) {
                // saving the full AWS queue url into settings for later use
                $queue->setOption(SqsQueue::OPTION_QUEUE_URL, $result->get('QueueUrl'));
                $this->getServiceManager()->register(Queue::SERVICE_ID, $queue);

                return \common_report_Report::createSuccess('Queue has been successfully created and queue url "'. $result->get('QueueUrl') .'" has been saved.');
            } else {
                return \common_report_Report::createFailure('No queue created.');
            }
        } catch (AwsException $e) {
            return \common_report_Report::createFailure($e->getAwsErrorMessage());
        }
    }
}