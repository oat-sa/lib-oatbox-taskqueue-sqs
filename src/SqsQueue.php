<?php

namespace oat\TaskSqsQueue;

use oat\oatbox\task\AbstractQueue;
use oat\TaskSqsQueue\Exception\SqsTaskQueueException;
use oat\TaskSqsQueue\Persistence\AwsMessageList;
use oat\TaskSqsQueue\Persistence\SqsPersistence;

class SqsQueue extends AbstractQueue
{
    const OPTION_QUEUE_NAME = 'queue_name';
    const OPTION_QUEUE_URL = 'queue_url';

    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->persistence->setQueueUrl($this->getOption(self::OPTION_QUEUE_URL));
    }

    /**
     * @param      $action
     * @param      $parameters
     * @param bool $repeatedly
     * @param null $label
     * @param null $type
     * @return SyncTask|void
     */
    public function createTask($action, $parameters, $repeatedly = false, $label = null, $type = null)
    {
        if ($repeatedly) {
            return;
        }

        $task = new AwsTask($action, $parameters);
        $task->setLabel($label);
        $task->setType($type);

        if (!$this->getOption(self::OPTION_QUEUE_URL)) {
            throw new \LogicException("Queue url is not set.");
        }

        $this->getPersistence()
            ->add($task);

        return $task;
    }

    /**
     * @return AwsMessageList
     */
    public function getIterator()
    {
        return new AwsMessageList($this->getPersistence()->receiveMessage());
    }

    /**
     * @return SqsPersistence
     */
    public function getPersistence()
    {
        return parent::getPersistence();
    }

    /**
     * @throws SqsTaskQueueException
     */
    public function getPayload($currentUserId)
    {
        throw new SqsTaskQueueException('Payload mechanism is not possible for AWS SQS');
    }
}