<?php namespace PHPQ;

use DateInterval;
use DateTime;
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
     * @var DateTimeImmutable
     */
    private $_finished = null;

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
     * Internally used to store whether we should retry this job.
     *
     * @var int
     */
    private $_retry = false;

    /**
     * Internally used to store the number of times this job has been retried.
     *
     * @var int
     */
    private $_retryCount = 0;

    /**
     * Internally used to store the period of time to wait before retrying this
     * job.
     *
     * @var DateInterval
     */
    private $_retryAfter = null;

    /**
     * Internally used to store whether this job has been deferred until a later
     * time.
     *
     * @var boolean
     */
    private $_deferred = false;

    /**
     * Internally used to store when this job has been deferred until.
     *
     * @var DateTimeImmutable
     */
    private $_deferredUntil = null;

    /**
     * Internally used to store when this job was last attempted.
     *
     * @var null|DateTimeImmutable
     */
    private $_lastAttempt = null;

    /**
     * Internally used to determine when execution of this job will timeout.
     *
     * @var null|DateTimeImmutable
     */
    private $_timeout = null;

    /**
     * Internally used for locking - keeps track of whether other processes have
     * accessed this job.
     *
     * @var int
     */
    private $_version = 0;

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
    abstract public function perform(ContainerInterface $container = null);

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
        $this->_finished = new DateTimeImmutable();

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

    /**
     * Mark this job as being unavailable to be retried.
     *
     * @return $this
     */
    final protected function withoutRetry()
    {
        $this->_retry = false;

        return $this;
    }

    /**
     * Mark this job as being unavailable to be retried.
     *
     * @return $this
     */
    final protected function thenRetry()
    {
        $this->_retry = true;

        return $this;
    }

    /**
     * Get the number of times this job has been retried.
     *
     * @return int
     */
    final public function getRetryCount()
    {
        return $this->_retryCount;
    }

    /**
     * Mark this job to be retried after the specified interval.
     *
     * @param DateInterval $interval
     *
     * @return $this
     * @throws Exception
     */
    final protected function after(DateInterval $interval)
    {
        if (!$this->_retry) {
            throw new Exception('Called after() without thenRetry()');
        }

        $this->_retryAfter = $interval;

        return $this;
    }

    /**
     * Mark this job as deferred until the specified time.
     *
     * @param DateTimeImmutable $when
     *
     * @return $this
     */
    final protected function deferUntil(DateTimeImmutable $when)
    {
        $this->_deferred = true;
        $this->_deferredUntil = $when;

        return $this;
    }

    /**
     * Mark this job as deferred for the specified interval
     *
     * @param DateInterval $interval
     *
     * @return $this
     */
    final protected function deferFor(DateInterval $interval)
    {
        $when = DateTimeImmutable::createFromMutable((new DateTime())->add($interval));

        $this->_deferred = true;
        $this->_deferredUntil = $when;

        return $this;
    }

    /**
     * Called before a job is run.
     *
     * @return void
     */
    public function setUp()
    {
        // NOP
    }

    /**
     * Called after a job has executed - successfully or not.
     */
    public function tearDown()
    {
        // NOP
    }

    /**
     * Returns the default amount of time to wait before execution of this job
     * times-out.
     *
     * @return DateInterval
     */
    public function getDefaultTimeoutInterval()
    {
        // Fifteen (15) minutes by default
        return new DateInterval('PT15M');
    }
}
