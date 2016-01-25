<?php namespace PHPQ\Driver;

use PHPQ\Job;
use PHPQ\Queue;
use PHPQ\Reflection\JobReflector;

/**
 * A basic in-memory driver intended to be used for testing and development
 * purposes only.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class ArrayDriver extends AbstractDriver
{
    protected $queues = array();

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'array';
    }

    /**
     * @inheritdoc
     */
    public function countPendingJobsInQueue(Queue $queue)
    {
        $queue->getPHPQ()->getLogger()->debug(
            sprintf('Counting jobs in [%s]', $queue->getName())
        );

        $count = 0;

        foreach ($this->getQueue($queue->getName()) as $job) {
            if (JobReflector::didFinish($job)) {
                $count ++;
            }
        }

        return $count;
    }

    /**
     * Helper function to get the named queue array internally.
     *
     * @param $name
     *
     * @return Job[]
     */
    private function getQueue($name)
    {
        if (!array_key_exists($name, $this->queues)) {
            $this->queues[$name] = array();
        }

        return $this->queues[$name];
    }
}
