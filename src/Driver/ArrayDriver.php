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
    /**
     * An array of Queues containing Jobs.
     *
     * @var array
     */
    protected $queues = array();

    /**
     * Keep track of the number of jobs we have so we can assign each one a
     * unique ID.
     *
     * @var int
     */
    protected $counter = 0;

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
            if (!property_exists($job, '_started')) {
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

    /**
     * @inheritdoc
     */
    public function addJobToQueue(Queue $queue, Job &$job)
    {
        $this->getQueue($queue->getName());

        array_push($this->queues[$queue->getName()], $job);

        return $this->counter ++;
    }

    /**
     * @inheritdoc
     */
    public function reserveJob(
        $queues = null,
        $blocking = true,
        $timeout = 0
    ) {
        $firstIteration = true;
        $end = time() + $timeout;

        while (($blocking || $firstIteration) && ($timeout == 0 || time() < $end)) {
            foreach ($this->queues as $name => &$queue) {
                if ($queues != null && !in_array($name, $queues)) {
                    continue;
                }

                foreach ($queue as &$job) {
                    if (!property_exists($job, '_started')) {
                        $job->_started = true;

                        return $job;
                    }
                }
            }

            if ($timeout > 0) {
                sleep (1);
            }

            $firstIteration = false;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function markJobAsFinished(Job &$job)
    {
        // NOP
    }

    /**
     * @inheritdoc
     */
    public function markJobAsFinishedWithResult(Job &$job, $result)
    {
        // NOP
    }

    /**
     * @inheritdoc
     */
    public function markJobAsFailed(Job &$job)
    {
        // NOP
    }

    /**
     * @inheritdoc
     */
    public function markJobAsFailedWithResult(Job &$job, $result)
    {
        // NOP
    }
}
