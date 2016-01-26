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
     * Persist the given jobs state.
     *
     * @param Job $job
     */
    abstract public function persistJobState(Job &$job);

    /**
     * Report to this driver that we are finished with the given job.
     *
     * @param Job $job
     */
    abstract public function detach(Job &$job);

    /**
     * Report the progress from 0 - 100 for this job.
     *
     * @param Job   $job
     * @param float $progress
     */
    abstract public function reportJobProgress(Job &$job, $progress);

    /**
     * Get a job by its ID.
     *
     * @param $id
     *
     * @return Job|null
     */
    abstract public function getJobById($id);

    /**
     * This function will be called to set up the queueing system for first-use.
     * It can be used to perform database schema creation etc.
     */
    public function performInitialSetup()
    {
        //
    }
}