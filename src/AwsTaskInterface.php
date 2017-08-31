<?php

namespace oat\TaskSqsQueue;


interface AwsTaskInterface
{
    public function setMessageId($id);

    public function getMessageId();

    public function setReceiptHandle($value);

    public function getReceiptHandle();

    public function setReceiveCount($value);

    public function getReceiveCount();

    public function hasBeenReceived();
}