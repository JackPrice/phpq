<?php namespace PHPQ\Driver;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPQ\Exception;
use PHPQ\Exception\ReflectionException;
use PHPQ\Job;
use PHPQ\Queue;
use PHPQ\Reflection\JobReflector;

/**
 * A driver that uses Doctrine's DBAL as its back-end.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class DoctrineDBALDriver extends AbstractDriver
{
    const POLL_INTERVAL = 5;

    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string[]
     */
    private $columns = [
        '__CLASS__'                         => 'class',
        JobReflector::PROPERTY_ID           => 'id',
        JobReflector::PROPERTY_QUEUE        => 'queue',
        JobReflector::PROPERTY_CREATED      => 'created',
        JobReflector::PROPERTY_SCHEDULE     => 'schedule',
        JobReflector::PROPERTY_FAILED       => 'failed',
        JobReflector::PROPERTY_FINISHED     => 'finished',
        JobReflector::PROPERTY_PROGRESS     => 'progress',
        JobReflector::PROPERTY_RESULT       => 'result',
        JobReflector::PROPERTY_RETRY        => 'retry',
        JobReflector::PROPERTY_RETRY_COUNT  => 'retry_count',
        JobReflector::PROPERTY_LAST_ATTEMPT => 'last_attempt',
        JobReflector::PROPERTY_TIMEOUT      => 'timeout',
        JobReflector::PROPERTY_VERSION      => 'version',
        JobReflector::PROPERTY_PARAMETERS   => 'parameters',
    ];

    /**
     * Internally keep the original state of all jobs we currently manage so we
     * can compute diffs against them.
     *
     * @var Job[]
     */
    private $jobs = [];

    /**
     * DoctrineDBALDriver constructor.
     *
     * @param Connection $connection
     * @param string     $tablePrefix
     */
    public function __construct(Connection $connection, $tablePrefix = '')
    {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;

        $connection->connect();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'doctrine_dbal';
    }

    /**
     * @inheritdoc
     */
    public function countPendingJobsInQueue(Queue $queue)
    {
        $stmt = $this->connection->prepare(
            'SELECT COUNT(*) AS `count` FROM ' . $this->getQueueTableName() . ' ' .
            'WHERE NOT ' . $this->columns[JobReflector::PROPERTY_FAILED] . ' ' .
            'AND ' . $this->columns[JobReflector::PROPERTY_FINISHED] . ' IS NULL ' .
            'AND ' . $this->columns[JobReflector::PROPERTY_SCHEDULE] . ' <= :now'
        );

        $stmt->execute([
            'now' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get the name of the table used for storing jobs.
     *
     * @return string
     */
    private function getQueueTableName()
    {
        return $this->tablePrefix . 'job_queue';
    }

    /**
     * @inheritdoc
     */
    public function addJobToQueue(Queue $queue, Job &$job)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->insert($this->getQueueTableName())
            ->values([
                $this->columns['__CLASS__']                         => ':class',
                $this->columns[JobReflector::PROPERTY_QUEUE]        => ':queue',
                $this->columns[JobReflector::PROPERTY_CREATED]      => ':created',
                $this->columns[JobReflector::PROPERTY_SCHEDULE]     => ':schedule',
                $this->columns[JobReflector::PROPERTY_FAILED]       => ':failed',
                $this->columns[JobReflector::PROPERTY_FINISHED]     => ':finished',
                $this->columns[JobReflector::PROPERTY_RESULT]       => ':result',
                $this->columns[JobReflector::PROPERTY_PROGRESS]     => ':progress',
                $this->columns[JobReflector::PROPERTY_LAST_ATTEMPT] => ':lastAttempt',
                $this->columns[JobReflector::PROPERTY_TIMEOUT]      => ':timeout',
                $this->columns[JobReflector::PROPERTY_RETRY_COUNT]  => ':retryCount',
                $this->columns[JobReflector::PROPERTY_RETRY]        => ':retry',
                $this->columns[JobReflector::PROPERTY_VERSION]      => ':version',
                $this->columns[JobReflector::PROPERTY_PARAMETERS]   => ':parameters',
            ])
            ->setParameters([
                'class'       => get_class($job),
                'queue'       => $queue->getName(),
                'created'     => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'schedule'    => JobReflector::getSchedule($job) ? JobReflector::getSchedule($job)->format('Y-m-d H:i:s') : (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'failed'      => false,
                'finished'    => null,
                'result'      => null,
                'progress'    => null,
                'lastAttempt' => null,
                'timeout'     => null,
                'retryCount'  => 0,
                'retry'       => false,
                'version'     => JobReflector::getVersion($job),
                'parameters'  => json_encode(JobReflector::getParameters($job)),
            ]);

        $qb->execute();

        return $this->connection->lastInsertId('job_seq');
    }

    /**
     * @inheritdoc
     */
    public function reserveJob(
        $queues = null,
        $blocking = true,
        $timeout = 0
    ) {
        if ($blocking) {
            return $this->reserveJobBlocking($queues, $timeout);
        } else {
            return $this->reserveJobNonBlocking($queues);
        }
    }

    /**
     * Reserve a job in blocking mode.
     *
     * @see DoctrineDBALDriver::reserveJob
     *
     * @param string[]|null $queues
     * @param int           $timeout
     *
     * @return null|Job
     */
    private function reserveJobBlocking($queues, $timeout)
    {
        $end = time() + $timeout;

        while ($timeout === 0 || time() < $end) {
            $job = $this->reserveJobNonBlocking($queues);

            if ($job) {
                return $job;
            }

            sleep(static::POLL_INTERVAL);
        }

        return null;
    }

    /**
     * Reserve a job in non-blocking mode.
     *
     * @see DoctrineDBALDriver::reserveJob
     *
     * @param string[]|null $queues
     *
     * @return Job|null
     */
    private function reserveJobNonBlocking($queues)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select('*')
            ->from($this->getQueueTableName())
            ->where($qb->expr()->lte(
                $this->columns[JobReflector::PROPERTY_SCHEDULE],
                ':now'
            ))
            ->andWhere($qb->expr()->eq(
                $this->columns[JobReflector::PROPERTY_FAILED],
                ':false'
            ))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull($this->columns[JobReflector::PROPERTY_LAST_ATTEMPT]),
                $qb->expr()->lte(
                    $this->columns[JobReflector::PROPERTY_TIMEOUT],
                    ':now'
                )
            ))
            ->andWhere(
                $qb->expr()->isNull($this->columns[JobReflector::PROPERTY_FINISHED])
            )
            ->setParameters([
                'now' => (new DateTime())->format('Y-m-d H:i:s'),
                'false' => false,
            ])
            ->setMaxResults(1);

        $data = $qb->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $job = $this->hydrateJobObject($data);

        $now = new DateTimeImmutable();
        $timeout = $now->add($job->getDefaultTimeoutInterval());

        $version = JobReflector::getVersion($job);

        $qb
            ->update($this->getQueueTableName())
            ->set($this->columns[JobReflector::PROPERTY_LAST_ATTEMPT], ':now')
            ->set($this->columns[JobReflector::PROPERTY_VERSION], ':newVersion')
            ->set($this->columns[JobReflector::PROPERTY_TIMEOUT], ':timeout')
            ->where($qb->expr()->eq(
                $this->columns[JobReflector::PROPERTY_ID],
                ':id'
            ))
            ->andWhere($qb->expr()->eq(
                $this->columns[JobReflector::PROPERTY_VERSION],
                ':version'
            ))
            ->setParameters([
                'id'         => $job->getId(),
                'now'        => $now->format('Y-m-d H:i:s'),
                'timeout'    => $timeout->format('Y-m-d H:i:s'),
                'version'    => $version,
                'newVersion' => $version + 1
            ]);

        $result = $qb->execute();

        if ($result === 1) {
            JobReflector::setVersion($job, $version + 1);
            JobReflector::setLastAttempt($job, $now);
            JobReflector::setTimeout($job, $timeout);

            return $job;
        } else {
            return $this->reserveJobNonBlocking($queues);
        }
    }

    /**
     * @inheritdoc
     */
    public function persistJobState(Job &$job)
    {
        if (!array_key_exists(spl_object_hash($job), $this->jobs)) {
            throw new ReflectionException(
                sprintf('Untracked job reported')
            );
        }

        $original = $this->jobs[spl_object_hash($job)];
        $version = JobReflector::getVersion($job);

        $qb = $this->connection->createQueryBuilder();

        $qb
            ->update($this->getQueueTableName())
            ->set($this->columns[JobReflector::PROPERTY_VERSION], ':newVersion')
            ->where($qb->expr()->eq(
                $this->columns[JobReflector::PROPERTY_ID],
                ':id'
            ))
            ->andWhere($qb->expr()->eq(
                $this->columns[JobReflector::PROPERTY_VERSION],
                ':version'
            ));

        $qb->setParameters([
            'id' => $job->getId(),
            'version' => $version,
            'newVersion' => $version + 1
        ]);

        foreach ($this->columns as $property => $column) {
            if ($property == '__CLASS__') {
                continue;
            }

            $old = JobReflector::getProperty($original, $property);
            $new = JobReflector::getProperty($job, $property);

            if ($old != $new) {
                if ($new instanceof DateTimeImmutable) {
                    $new = $new->format('Y-m-d H:i:s');
                } elseif (is_array($new)) {
                    $new = json_encode($new);
                }

                $qb->set($column, $qb->createNamedParameter($new));
            }
        }

        $result = $qb->execute();

        if ($result === 1) {
            JobReflector::setVersion($job, $version + 1);

            $this->jobs[spl_object_hash($job)] = clone $job;

            return $job;
        } else {
            throw new Exception\LockException(
                sprintf('Could not lock job #%d', $job->getId())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function detach(Job &$job)
    {
        if (!array_key_exists(spl_object_hash($job), $this->jobs)) {
            throw new ReflectionException(
                sprintf('Attempted to detach untracked job')
            );
        }

        unset($this->jobs[spl_object_hash($job)]);

        return;
    }


    /**
     * @inheritdoc
     */
    public function getJobById($id)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select('*')
            ->from($this->getQueueTableName())
            ->where($qb->expr()->eq(
                $this->columns[JobReflector::PROPERTY_ID],
                ':id'
            ))
            ->setMaxResults(1);

        $data = $qb->execute()->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrateJobObject($data);
    }

    /**
     * @inheritdoc
     */
    public function reportJobProgress(Job &$job, $progress)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->update($this->getQueueTableName(), 'job')
            ->set('job.progress', ':progress')
            ->where('job.id = :id')
            ->setParameters([
                'id'       => $job->getId(),
                'progress' => $progress,
            ])
            ->execute();

        return;
    }

    /**
     * Create a job from te given column set.
     *
     * @param $columns
     *
     * @return Job
     * @throws ReflectionException
     */
    private function hydrateJobObject($columns)
    {
        // Figure out the class of the job
        $class = $columns[$this->columns['__CLASS__']];

        if (!class_exists($class)) {
            throw new ReflectionException(
                sprintf('Job class [%s] does not exist', $class)
            );
        }

        try {
            $job = new $class();
        } catch (\Exception $e) {
            throw new ReflectionException(
                sprintf('Could not instantiate object of type [%s]', $class)
            );
        }

        if (!($job instanceof Job)) {
            throw new ReflectionException(
                sprintf('[%s] is not an instance of %s', $class, Job::class)
            );
        }

        $queue = $this->getPHPQ()->getQueue($columns[$this->columns[JobReflector::PROPERTY_QUEUE]]);
        $parameters = $columns[$this->columns[JobReflector::PROPERTY_PARAMETERS]];
        $result = $columns[$this->columns[JobReflector::PROPERTY_RESULT]];
        $timeout = $columns[$this->columns[JobReflector::PROPERTY_TIMEOUT]];
        $lastAttempt = $columns[$this->columns[JobReflector::PROPERTY_LAST_ATTEMPT]];

        JobReflector::setId(
            $job,
            $columns[$this->columns[JobReflector::PROPERTY_ID]]
        );
        JobReflector::setQueue(
            $job,
            $queue
        );
        JobReflector::setCreated(
            $job,
            new DateTimeImmutable($columns[$this->columns[JobReflector::PROPERTY_CREATED]])
        );
        JobReflector::setSchedule(
            $job,
            new DateTimeImmutable($columns[$this->columns[JobReflector::PROPERTY_SCHEDULE]])
        );
        JobReflector::setFailed(
            $job,
            $columns[$this->columns[JobReflector::PROPERTY_FAILED]]
        );
        JobReflector::setFinished(
            $job,
            $columns[$this->columns[JobReflector::PROPERTY_FINISHED]]
        );
        JobReflector::setResult(
            $job,
            $result ? json_decode($result, true) : null
        );
        JobReflector::setProgress(
            $job,
            $columns[$this->columns[JobReflector::PROPERTY_PROGRESS]]
        );
        JobReflector::setLastAttempt(
            $job,
            $lastAttempt ? new DateTimeImmutable($lastAttempt) : null
        );
        JobReflector::setTimeout(
            $job,
            $timeout ? new DateTimeImmutable($timeout) : null
        );
        JobReflector::setRetryCount(
            $job,
            $columns[$this->columns[JobReflector::PROPERTY_RETRY_COUNT]]
        );
        JobReflector::setParameters(
            $job,
            $parameters ? json_decode($parameters, true) : array()
        );

        // Track the state of this job
        $this->jobs[spl_object_hash($job)] = clone $job;

        return $job;
    }

    /**
     * @inheritdoc
     */
    public function performInitialSetup()
    {
        $manager = $this->connection->getSchemaManager();

        $from = $manager->createSchema();
        $to = clone $from;

        $table = $to->createTable($this->getQueueTableName());

        $to->createSequence('job_seq');

        $table->addColumn($this->columns[JobReflector::PROPERTY_ID], 'integer', ['autoincrement' => true]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_QUEUE], 'string');
        $table->addColumn($this->columns[JobReflector::PROPERTY_CREATED], 'datetime');
        $table->addColumn($this->columns[JobReflector::PROPERTY_SCHEDULE], 'datetime');
        $table->addColumn($this->columns[JobReflector::PROPERTY_FAILED], 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_FINISHED], 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_RESULT], 'text', ['notnull' => false, 'default' => null]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_PROGRESS], 'decimal', ['notnull' => false, 'default' => null, 'precision' => 5, 'scale' => 2]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_LAST_ATTEMPT], 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_TIMEOUT], 'datetime', ['notnull' => false, 'default' => null]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_RETRY_COUNT], 'integer');
        $table->addColumn($this->columns[JobReflector::PROPERTY_RETRY], 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_PARAMETERS], 'text', ['notnull' => false, 'default' => null]);
        $table->addColumn($this->columns[JobReflector::PROPERTY_VERSION], 'integer');
        $table->addColumn($this->columns['__CLASS__'], 'string');

        $table->setPrimaryKey(array($this->columns[JobReflector::PROPERTY_ID]));

        $sql = $from->getMigrateToSql($to, $this->connection->getDatabasePlatform());

        $this->connection->beginTransaction();

        foreach ($sql as $query) {
            $this->connection->exec($query);
        }

        $this->connection->commit();

        return;
    }
}