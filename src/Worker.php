<?php namespace PHPQ;

use PHPQ\Reflection\JobReflector;

/**
 * A worker that handles checking for jobs, fetching them from queues, running
 * them and handling the result.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class Worker
{
    /**
     * The PHPQ instance this queue belongs to.
     *
     * @var PHPQ
     */
    protected $phpq;

    /**
     * The queues that this worker will handle, or none if we will handle any
     * queue.
     *
     * @var string[]|null
     */
    protected $queues;

    /**
     * Whether we should shut down on the next cycle.
     *
     * @var boolean
     */
    private $shutdown = false;

    /**
     * Worker constructor.
     *
     * @param PHPQ   $phpq
     * @param string $queues
     */
    public function __construct(PHPQ $phpq, $queues = null)
    {
        $this->phpq = $phpq;
        $this->queues = $queues;
    }

    /**
     * Get the PHPQ instance this queue belongs to.
     *
     * @return PHPQ
     */
    public function getPHPQ()
    {
        return $this->phpq;
    }

    /**
     * Get a job from the top of the queues we handle and mark it as started.
     * If $blocking, this operation will not return until we
     * have a job, or $timeout seconds have elapsed.
     *
     * @param bool $blocking
     * @param int  $timeout
     *
     * @return Job|null
     */
    public function reserve($blocking = true, $timeout = 0)
    {
        return $this->getPHPQ()
            ->getDriver()
            ->reserveJob($this->queues, $blocking, $timeout);
    }

    /**
     * Attempt to run the given job.
     *
     * @param Job $job
     *
     * @return void
     */
    public function run(Job &$job)
    {
        $this->getPHPQ()->getLogger()->info(sprintf('Running job #%d', $job->getId()));

        JobReflector::setFailed($job, false);
        JobReflector::setFinished($job, null);
        JobReflector::setHasResult($job, false);
        JobReflector::setResult($job, null);

        try {
            $job->setUp();
            $job->perform($this->getPHPQ()->getContainer());
            $job->tearDown();
        } catch (\Exception $e) {
            JobReflector::setFailed($job, true);
        }

        $this->getPHPQ()->getDriver()->persistJobState($job);
        $this->getPHPQ()->getDriver()->detach($job);

        return;
    }
}