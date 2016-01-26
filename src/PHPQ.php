<?php namespace PHPQ;

use Interop\Container\ContainerInterface;
use PHPQ\Driver\AbstractDriver;
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
    const DEFAULT_QUEUE = '_default';

    /**
     * A container used for passing objects and services to jobs.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The current driver.
     *
     * @var AbstractDriver
     */
    protected $driver;

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

    /**
     * Get the current container implementation.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the current container implementation.
     *
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the current driver implementation.
     *
     * @return AbstractDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set the current driver implementation.
     *
     * @param AbstractDriver $driver
     *
     * @return $this
     */
    public function setDriver(AbstractDriver $driver)
    {
        $driver->setPHPQ($this);

        $this->driver = $driver;

        $this->getLogger()->debug(
            sprintf('Added driver of type [%s]', $driver->getName())
        );

        return $this;
    }

    /**
     * Get a queue by its name, or the default queue if none specified.
     *
     * @param string $name
     *
     * @return Queue
     */
    public function getQueue($name = PHPQ::DEFAULT_QUEUE)
    {
        return new Queue($this, $name);
    }

    /**
     * Get a worker for the specified queues, or all queues if not specified.
     *
     * @param null|string[] $queues
     *
     * @return Worker
     */
    public function getWorker($queues = null)
    {
        return new Worker($this, $queues);
    }

    /**
     * Get a job by its ID.
     *
     * @param $id
     *
     * @return null|Job
     */
    public function getJobById($id)
    {
        return $this->getDriver()->getJobById($id);
    }
}
