<?php

namespace oat\TaskSqsQueue;

use oat\oatbox\task\AbstractTask;

class AwsTask extends AbstractTask implements AwsTaskInterface
{
    private $messageId;
    private $receiptHandle;

    /**
     * The number of times a message has been received from the queue but not deleted.
     *
     * @var int
     */
    private $receiveCount;

    /**
     * @param string $id
     * @return AwsTask
     */
    public function setMessageId($id)
    {
        $this->messageId = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param string $value
     * @return AwsTask
     */
    public function setReceiptHandle($value)
    {
        $this->receiptHandle = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiptHandle()
    {
        return $this->receiptHandle;
    }

    /**
     * @param string $value
     * @return AwsTask
     */
    public function setReceiveCount($value)
    {
        $this->receiveCount = (int) $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getReceiveCount()
    {
        return $this->receiveCount;
    }

    /**
     * Wheter the given message has been received before or not.
     *
     * @return bool
     */
    public function hasBeenReceived()
    {
        return $this->getReceiveCount() > 1;
    }
}