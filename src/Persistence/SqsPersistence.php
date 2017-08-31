<?php

namespace oat\TaskSqsQueue\Persistence;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use oat\oatbox\task\Exception\BadTaskQueueOption;
use oat\oatbox\task\Task;
use oat\oatbox\task\TaskInterface\LimitablePersistence;
use oat\oatbox\task\TaskInterface\TaskPersistenceInterface;
use oat\TaskSqsQueue\AwsTask;
use oat\TaskSqsQueue\Exception\SqsTaskQueueException;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SqsPersistence implements TaskPersistenceInterface, SqsPersistanceInterface, LimitablePersistence
{
    use ServiceLocatorAwareTrait;

    const OPTION_PROFILE = 'profile';
    const OPTION_REGION = 'region';
    const OPTION_VERSION = 'version';

    /**
     * @var SqsClient
     */
    private $sqsClient;

    /**
     * The entire queue URL returned by createQueue().
     *
     * @var string
     */
    private $queueUrl;

    private $returnMessageLimit = 10;

    public function __construct(array $config)
    {
        if (!isset($config[self::OPTION_PROFILE])) {
            throw new BadTaskQueueOption("Profile option needs to be set in config");
        }

        if (!isset($config[self::OPTION_REGION])) {
            throw new BadTaskQueueOption("Region option needs to be set in config");
        }

        if (!isset($config[self::OPTION_VERSION])) {
            $config[self::OPTION_VERSION] = 'latest';
        }

        $this->sqsClient = new SqsClient([
            'profile' => $config[self::OPTION_PROFILE],
            'region' => $config[self::OPTION_REGION],
            'version' => $config[self::OPTION_VERSION]
        ]);
    }

    public function add(Task $task)
    {
        try {
            $result = $this->sqsClient->sendMessage([
                'MessageAttributes' => [],
                'MessageBody' => json_encode($task),
                'QueueUrl' => $this->getQueuUrl()
            ]);

            if ($result->hasKey('MessageId')) {
               \common_Logger::d('Task '. $task->getId() .' successfully sent, SQS id: '. $result->get('MessageId'));
            } else {
                \common_Logger::e('Task '. $task->getId() .' seems not received by SQS.');
            }
        } catch (AwsException $e) {
            \common_Logger::e('AWS error during sending task '. $task->getId() .'. AWS msg: '. $e->getAwsErrorMessage());
        }
    }

    /**
     * Return max 10 (no more allowed) messages from SQS Queue.
     *
     * @return array
     */
    public function receiveMessage()
    {
        try {
            $result = $this->sqsClient->receiveMessage([
                'AttributeNames' => ['All'],
                'MaxNumberOfMessages' => $this->getReturnTaskLimit(),
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $this->getQueuUrl(),
                'WaitTimeSeconds' => 15
            ]);

            if (count($result->get('Messages')) > 0) {
                \common_Logger::d('Received '. count($result->get('Messages')) .' messages from queue.');
                return $result->get('Messages');
            } else {
                \common_Logger::d('No messages in queue.');
                return [];
            }
        } catch (AwsException $e) {
            \common_Logger::e('AWS error during receiving messages. AWS msg: '. $e->getAwsErrorMessage());
        } catch (\Exception $e) {
            \common_Logger::e('dddddddd '. $e->getMessage());
        }
    }

    /**
     * Delete a message (task) from queue.
     *
     * @param AwsTask $task
     */
    public function deleteMessage(AwsTask $task)
    {
        try {
            $this->sqsClient->deleteMessage([
                'QueueUrl' => $this->getQueuUrl(),
                'ReceiptHandle' => $task->getReceiptHandle()
            ]);

            \common_Logger::d('Task '. $task->getId() .' deleted from queue.');
        } catch (AwsException $e) {
            \common_Logger::e('Error during deleting task '. $task->getId() .'. AWS msg: '. $e->getAwsErrorMessage());
        }
    }

    /**
     * @return SqsClient
     */
    public function getSqsClient()
    {
        return $this->sqsClient;
    }

    /**
     * @param string $queueUrl
     * @return SqsPersistence
     */
    public function setQueueUrl($queueUrl)
    {
        $this->queueUrl = $queueUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getQueuUrl()
    {
        return $this->queueUrl;
    }

    /**
     * @param int $limit
     * @return SqsPersistence
     */
    public function setReturnTaskLimit($limit)
    {
        // Valid values are 1 to 10.
        $this->returnMessageLimit = $limit > 10 ? 10 : $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getReturnTaskLimit()
    {
        return $this->returnMessageLimit;
    }

    public function get($taskId)
    {
        throw new SqsTaskQueueException('Getting a task by its id is not possible for AWS SQS');
    }

    public function search(array $filterTask, $rows = null, $page = null, $sortBy = null, $sortOrder = null)
    {
        throw new SqsTaskQueueException('Search mechanism is not possible for AWS SQS');
    }

    public function has($taskId)
    {
        throw new SqsTaskQueueException('Checking of the existence of a task is not possible for AWS SQS');
    }

    public function update($taskId, $status)
    {
        throw new SqsTaskQueueException('Updating task is not possible for AWS SQS');
    }

    public function setReport($taskId, \common_report_Report $report)
    {
        throw new SqsTaskQueueException('Report mechanism is not possible for AWS SQS');
    }

    public function count(array $params)
    {
        throw new SqsTaskQueueException('Counting tasks is not possible for AWS SQS');
    }

    public function getAll()
    {
        throw new SqsTaskQueueException('Getting all tasks is not possible for AWS SQS');
    }
}