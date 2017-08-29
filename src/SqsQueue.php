<?php

namespace oat\TaskSqsQueue;

use oat\oatbox\task\AbstractQueue;

class SqsQueue extends AbstractQueue
{
    const OPTION_QUEUE_NAME = 'qeue_name';
}