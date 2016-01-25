<?php namespace PHPQ;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * This class is the "owning class" of all parts of PHPQ. It represents all
 * queues and jobs with a single driver.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class PHPQ implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const VERSION = '0.1';

    /**
     * The current PSR-3-compatible logger implementation.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Get the current logger implementation (defaults to a null logger).
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $this->logger = new \Psr\Log\NullLogger();
        }

        return $this->logger;
    }
}
