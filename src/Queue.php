<?php namespace PHPQ;

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
     * @param PHPQ   $PHPQ
     * @param string $name
     */
    public function __construct(PHPQ &$PHPQ, $name)
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
}
