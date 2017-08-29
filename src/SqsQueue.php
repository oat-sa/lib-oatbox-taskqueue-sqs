<?php

namespace oat\TaskSqsQueue;

use oat\oatbox\task\AbstractQueue;

class SqsQueue extends AbstractQueue
{
    const OPTION_QUEUE_NAME = 'queue_name';

    public function createTask($actionId, $parameters, $repeatedly = false, $label = null, $task = null)
    {
        // TODO: Implement createTask() method.
    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }
}