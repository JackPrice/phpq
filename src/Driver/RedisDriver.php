<?php namespace PHPQ\Driver;

use PHPQ\Job;
use PHPQ\Queue;
use PHPQ\Reflection\JobReflector;
use Predis;

/**
 * A driver that uses redis as its backend.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class RedisDriver extends AbstractDriver
{
    /**
     * The current redis client instance.
     *
     * @var Predis\Client;
     */
    protected $client;

    /**
     * RedisDriver constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->client = new Predis\Client($config);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'redis';
    }

    /**
     * Get the key for the given queue.
     *
     * @param Queue $queue
     *
     * @return string
     */
    private function getKeyForQueue(Queue $queue)
    {
        return $this->getQueueKeyPrefix() . $queue->getName();
    }

    /**
     * Get a queue object for the given key.
     *
     * @param $key
     *
     * @return Queue
     */
    private function getQueueForKey($key)
    {
        $queue = str_replace($this->getQueueKeyPrefix(), '', $key);

        return $this->phpq->getQueue($queue);
    }

    /**
     * Get the prefix for queue keys.
     *
     * @return string
     */
    private function getQueueKeyPrefix()
    {
        return 'queue-';
    }

    /**
     * Returns the keys for all queues.
     *
     * @return array
     */
    private function getQueueKeyNames()
    {
        return $this->client->keys($this->getQueueKeyPrefix() . '*');
    }

    /**
     * @inheritdoc
     */
    public function countPendingJobsInQueue(Queue $queue)
    {
        return $this->client->llen($this->getKeyForQueue($queue));
    }

    /**
     * @inheritdoc
     */
    public function addJobToQueue(Queue $queue, Job &$job)
    {
        $json = JobReflector::toJSON($job);

        $this->client->lpush($this->getKeyForQueue($queue), $json);

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function reserveJob(
        $queues = null,
        $blocking = true,
        $timeout = 0
    ) {
        if ($queues) {
            $queues = array_map(function ($queue) {
                return $this->getQueueKeyPrefix() . $queue;
            }, $queues);
        } else {
            $queues = $this->getQueueKeyNames();
        }

        if ($blocking) {
            $output = $this->client->blpop($queues, $timeout);

            if (!$output) {
                return null;
            }

            list($key, $json) = $output;

            $job = JobReflector::fromJSON($json);

            JobReflector::setQueue($job, $this->getQueueForKey($key));

            return $job;
        } else {
            foreach ($queues as $key) {
                $json = $this->client->lpop($queues);

                if ($json) {
                    $job = JobReflector::fromJSON($json);

                    JobReflector::setQueue($job, $this->getQueueForKey($key));

                    return $job;
                }

            }

            return null;
        }
    }

    /**
     * Mark the given job as finished.
     *
     * @param Job $job
     */
    public function markJobAsFinished(Job &$job)
    {
        // TODO: Implement markJobAsFinished() method.
    }

    /**
     * Mark the given job as finished with the given result.
     *
     * @param Job   $job
     * @param mixed $result
     */
    public function markJobAsFinishedWithResult(Job &$job, $result)
    {
        // TODO: Implement markJobAsFinishedWithResult() method.
    }

    /**
     * Mark the given job as failed.
     *
     * @param Job $job
     */
    public function markJobAsFailed(Job &$job)
    {
        // TODO: Implement markJobAsFailed() method.
    }

    /**
     * Mark the given job as failed with the given result.
     *
     * @param Job   $job
     * @param mixed $result
     */
    public function markJobAsFailedWithResult(Job &$job, $result)
    {
        // TODO: Implement markJobAsFailedWithResult() method.
    }

    /**
     * Get a job by its ID.
     *
     * @param $id
     *
     * @return Job|null
     */
    public function getJobById($id)
    {
        // TODO: Implement getJobById() method.
    }
}