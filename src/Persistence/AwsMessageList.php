<?php

namespace oat\TaskSqsQueue\Persistence;

use oat\oatbox\task\implementation\TaskList;
use oat\TaskSqsQueue\AwsTask;

class AwsMessageList extends TaskList
{
    /**
     * @return AwsTask
     */
    public function current()
    {
        /** @see http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#shape-message */
        $message = parent::current();

        $data = json_decode($message['Body'], true);

        if (is_array($data)) {
            /** @var AwsTask $awsTask */
            $awsTask = AwsTask::restore($data);
        } else {
            // if we don't have a properly json-encoded message body, returning just an "empty" task
            $awsTask = new AwsTask();
        }

        $awsTask->setMessageId($message['MessageId'])
            ->setReceiptHandle($message['ReceiptHandle']);

        return $awsTask;
    }
}