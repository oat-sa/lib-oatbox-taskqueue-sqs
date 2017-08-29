<?php

namespace oat\TaskSqsQueue\Persistence;

use Aws\Sqs\SqsClient;
use oat\oatbox\task\Exception\BadTaskQueueOption;
use oat\oatbox\task\Task;
use oat\oatbox\task\TaskInterface\TaskPersistenceInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SqsPersistence implements TaskPersistenceInterface, SqsPersistanceInterface
{
    use ServiceLocatorAwareTrait;

    const OPTION_PROFILE = 'profile';
    const OPTION_REGION = 'region';
    const OPTION_VERSION = 'version';

    private $sqsClient;

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

    public function get($taskId)
    {
        // TODO: Implement get() method.
    }

    public function add(Task $task)
    {
        // TODO: Implement add() method.
    }

    public function search(array $filterTask, $rows = null, $page = null, $sortBy = null, $sortOrder = null)
    {
        // TODO: Implement search() method.
    }

    public function has($taskId)
    {
        // TODO: Implement has() method.
    }

    public function update($taskId, $status)
    {
        // TODO: Implement update() method.
    }

    public function setReport($taskId, \common_report_Report $report)
    {
        // TODO: Implement setReport() method.
    }

    public function count(array $params)
    {
        // TODO: Implement count() method.
    }

    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    public function getSqsClient()
    {
        return $this->sqsClient;
    }
}