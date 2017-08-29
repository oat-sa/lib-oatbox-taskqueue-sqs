<?php

namespace oat\TaskSqsQueue\Persistence;

use Aws\Sqs\SqsClient;

interface SqsPersistanceInterface
{
    /**
     * @return SqsClient
     */
    public function getSqsClient();
}