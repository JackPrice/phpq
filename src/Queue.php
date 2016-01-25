<?php namespace PHPQ;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * This class represents a named job queue - containing any number of jobs.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class Queue implements \Countable
{
    /**
     * The PHPQ instance this queue belongs to.
     *
     * @var PHPQ
     */
    protected $phpq;

    /**
     * The name of this queue.
     *
     * @var string
     */
    protected $name;

    /**
     * Queue constructor.
     *
     * @param PHPQ   $phpq
     * @param string $name
     */
    public function __construct(PHPQ &$phpq, $name)
    {
        $this->phpq = &$phpq;
        $this->name = $name;
    }

    /**
     * Returns the number of pending jobs in this queue.
     *
     * @return int
     */
    public function count()
    {
        return $this->phpq->getDriver()->countPendingJobsInQueue($this);
    }

    /**
     * Returns the name of this queue.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Add the given job to this queue.
     *
     * @param Job $job
     *
     * @return $this
     */
    public function enqueue(Job &$job)
    {
        $this->initialiseJobObject($job);

        $this->phpq->getDriver()->addJobToQueue($this, $job);
    }

    /**
     * Schedule the given job for future execution in this queue.
     *
     * @param Job                                 $job
     * @param DateTimeImmutable $schedule
     *
     * @return $this
     */
    public function schedule(Job &$job, DateTimeImmutable $schedule)
    {
        $this->initialiseJobObject($job);

        Reflection\JobReflector::setSchedule($job, $schedule);

        $this->phpq->getDriver()->addJobToQueue($this, $job);
    }

    /**
     * Initialise the given job object.
     * @internal
     *
     * @param Job $job
     *
     * @return void
     */
    final private function initialiseJobObject(Job &$job)
    {
        $created = new DateTimeImmutable();

        Reflection\JobReflector::setQueue($job, $this);
        Reflection\JobReflector::setCreated($job, $created);
        Reflection\JobReflector::setSchedule($job, $created);

        return;
    }
}
