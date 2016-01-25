<?php namespace PHPQ\Driver;

use PHPQ\Job;
use PHPQ\PHPQ;
use PHPQ\Queue;

/**
 * All PHPQ drivers must concretely extend this class.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
abstract class AbstractDriver
{
    /**
     * The PHPQ instance this driver is attached to.
     *
     * @var PHPQ
     */
    protected $phpq;

    /**
     * Returns the PHPQ instance this driver is attached to.
     *
     * @return PHPQ
     */
    public function getPHPQ()
    {
        return $this->phpq;
    }

    /**
     * Set the PHPQ instance this driver is attached to.
     *
     * @param PHPQ $phpq
     *
     * @return $this
     */
    public function setPHPQ(PHPQ &$phpq)
    {
        $this->phpq = $phpq;

        return $this;
    }

    /**
     * Get the name of this driver.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Count the number of pending jobs in the given queue.
     *
     * @param Queue $queue
     *
     * @return int
     */
    abstract public function countPendingJobsInQueue(Queue $queue);

    /**
     * Push the given job onto the queue specified.
     * The driver is expected to initialise the ID of the job and return it.
     *
     * @param Queue $queue
     * @param Job   $job
     *
     * @return int
     */
    abstract public function addJobToQueue(Queue $queue, Job &$job);

    /**
     * Get a job from the top of the queues specified (or any if null) and mark
     * it as started.
     * If $blocking, this operation will not return until we
     * have a job, or $timeout seconds have elapsed.
     *
     * @param null $queues
     * @param bool $blocking
     * @param int  $timeout
     *
     * @return Job|null
     */
    abstract public function reserveJob(
        $queues = null,
        $blocking = true,
        $timeout = 0
    );

    /**
     * Mark the given job as finished.
     *
     * @param Job $job
     */
    abstract public function markJobAsFinished(Job &$job);

    /**
     * Mark the given job as finished with the given result.
     *
     * @param Job   $job
     * @param mixed $result
     */
    abstract public function markJobAsFinishedWithResult(Job &$job, $result);

    /**
     * Mark the given job as failed.
     *
     * @param Job $job
     */
    abstract public function markJobAsFailed(Job &$job);

    /**
     * Mark the given job as failed with the given result.
     *
     * @param Job   $job
     * @param mixed $result
     */
    abstract public function markJobAsFailedWithResult(Job &$job, $result);
}