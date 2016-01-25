<?php namespace PHPQ;

use DateTimeImmutable;
use Interop\Container\ContainerInterface;

/**
 * A single job in a queue.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
abstract class Job
{
    /**
     * The globally-unique ID of this job.
     *
     * @var int
     */
    protected $id;

    /**
     * The queue that this job belongs to.
     *
     * @var Queue
     */
    protected $queue;

    /**
     * When this job was first enqueued.
     *
     * @var DateTimeImmutable
     */
    protected $created;

    /**
     * When this job is scheduled to be ran.
     *
     * @var DateTimeImmutable
     */
    protected $schedule;

    /**
     * Parameters stored in this job.
     *
     * @var array
     */
    protected $parameters;

    /**
     * The class to run this job through.
     *
     * @var string
     */
    protected $class;

    /**
     * Internally used when this job is marked as failed.
     * @internal
     *
     * @var boolean
     */
    private $_failed = false;

    /**
     * Internally used when this job is marked as finished.
     * @internal
     *
     * @var boolean
     */
    private $_finished = false;

    /**
     * Internally used to mark this job as having a result.
     * @internal
     *
     * @var boolean
     */
    private $_hasResult = false;

    /**
     * Internally used when the execution of this job results in an output.
     * @internal
     *
     * @var mixed
     */
    private $_result = null;

    /**
     * Returns the globally-unique ID of this job.
     *
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the queue that this job belongs to.
     *
     * @return Queue
     */
    final public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Returns the DateTime when this job was first enqueued.
     *
     * @return DateTimeImmutable
     */
    final public function getCreated()
    {
        return $this->created;
    }

    /**
     * Returns when this job is scheduled to be ran.
     *
     * @return DateTimeImmutable
     */
    final public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Get the parameters stored in this job.
     *
     * @return array
     */
    final public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get a parameter stored in this job.
     *
     * @param      $name
     * @param null $default
     *
     * @return mixed
     */
    final public function getParameter($name, $default = null)
    {
        if (array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        } else {
            return $default;
        }
    }

    /**
     * Set the parameters stored in this job.
     *
     * @param array $parameters
     *
     * @return $this
     */
    final public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Set a parameter stored in this job.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    final public function setParameter($name, $value)
    {
        if (is_array($this->parameters)) {
            $this->parameters[$name] = $value;
        } else {
            $this->parameters = [
                $name => $value
            ];
        }

        return $this;
    }

    /**
     * Execute the task for this job.
     *
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    abstract public function perform(ContainerInterface $container);

    /**
     * Mark this job as failed.
     *
     * @return $this
     */
    final protected function fail()
    {
        $this->_failed = true;

        return $this;
    }

    /**
     * Mark this job as having finished execution.
     *
     * @return $this
     */
    final protected function finish()
    {
        $this->_finished = true;

        return $this;
    }

    /**
     * Provide data as the result of this job's execution.
     *
     * @param $data
     *
     * @return $this
     */
    final protected function withResult($data)
    {
        $this->_hasResult = true;
        $this->_result = $data;

        return $this;
    }
}
