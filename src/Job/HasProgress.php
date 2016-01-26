<?php namespace PHPQ\Job;

use PHPQ\Queue;

/**
 * Jobs implement this trait if they are capable of reporting incrememental
 * progress.
 *
 * @author Jack Price <jackprice@outlook.com>
 *
 * @method Queue getQueue()
 */
trait HasProgress
{
    /**
     * Used internally to store this job's progress.
     *
     * @var float
     */
    private $_progress = 0.0;

    /**
     * Report the progress of this job.
     *
     * @param float $progress The percentage completion of this job from 0 - 100.
     *
     * @return $this
     */
    protected function reportProgress($progress)
    {
        // Normalise to between 0 and 100
        $progress = max(min($progress, 100.0), 0.0);

        $this->_progress = $progress;

        $this->getQueue()
            ->getPHPQ()
            ->getDriver()
            ->reportJobProgress($this, $progress);

        return $this;
    }
}