<?php

namespace oat\TaskSqsQueue\Persistence;

use Aws\Sqs\SqsClient;
use oat\TaskSqsQueue\AwsTask;

interface SqsPersistanceInterface
{
    /**
     * @return SqsClient
     */
    public function getSqsClient();

    /**
     * Set the full AWS Queue Url
     * 
     * @param string $queueUrl
     */
    public function setQueueUrl($queueUrl);

    /**
     * @return string
     */
    public function getQueuUrl();

    /**
     * Get messages from the queue
     * @return array
     */
    public function receiveMessage();

    /**
     * Delete a message from the queue.
     *
     * @param AwsTask $task
     */
    public function deleteMessage(AwsTask $task);
}