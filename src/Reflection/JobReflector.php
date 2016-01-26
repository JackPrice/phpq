<?php namespace PHPQ\Reflection;

use DateTimeImmutable;
use PHPQ\Job;
use PHPQ\Queue;

/**
 * This class contains helper methods for reflecting the Job class.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
abstract class JobReflector extends Reflector
{
    const PROPERTY_ID = 'id';
    const PROPERTY_QUEUE = 'queue';
    const PROPERTY_CREATED = 'created';
    const PROPERTY_SCHEDULE = 'schedule';
    const PROPERTY_PARAMETERS = 'parameters';
    const PROPERTY_FAILED = '_failed';
    const PROPERTY_FINISHED = '_finished';
    const PROPERTY_RESULT = '_result';
    const PROPERTY_HAS_RESULT = '_hasResult';
    const PROPERTY_RETRY = '_retry';
    const PROPERTY_RETRY_COUNT = '_retryCount';
    const PROPERTY_LAST_ATTEMPT = '_lastAttempt';
    const PROPERTY_PROGRESS = '_progress';
    const PROPERTY_TIMEOUT = '_timeout';
    const PROPERTY_VERSION = '_version';

    /**
     * Set the ID on the given job.
     *
     * @param Job $job
     * @param int $id
     */
    public static function setId(Job &$job, $id)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_ID, $id);

        return;
    }

    /**
     * Set the queue on the given job.
     *
     * @param Job   $job
     * @param Queue $queue
     */
    public static function setQueue(Job &$job, Queue $queue)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_QUEUE, $queue);

        return;
    }

    /**
     * Set the created timestamp on the given job.
     *
     * @param Job                     $job
     * @param DateTimeImmutable $created
     */
    public static function setCreated(Job &$job, DateTimeImmutable $created)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_CREATED, $created);

        return;
    }

    /**
     * Get the created timestamp on the given job.
     *
     * @param Job $job
     *
     * @return DateTimeImmutable
     */
    public static function getCreated(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_CREATED);
    }

    /**
     * Set the schedule timestamp on the given job.
     *
     * @param Job               $job
     * @param DateTimeImmutable $schedule
     */
    public static function setSchedule(Job &$job, DateTimeImmutable $schedule)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_SCHEDULE, $schedule);

        return;
    }

    /**
     * Get the schedule timestamp on the given job.
     *
     * @param Job $job
     *
     * @return DateTimeImmutable
     */
    public static function getSchedule(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_SCHEDULE);
    }

    /**
     * Set the parameters on the given job.
     *
     * @param Job   $job
     * @param array $parameters
     */
    public static function setParameters(Job &$job, array $parameters = array())
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_PARAMETERS, $parameters);

        return;
    }

    /**
     * Get the parameters on the given job.
     *
     * @param Job $job
     *
     * @return array
     */
    public static function getParameters(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_PARAMETERS);
    }

    /**
     * Returns true if the given job marked itself as failed.
     *
     * @param Job $job
     *
     * @return boolean
     */
    public static function didFail(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_FAILED);
    }

    /**
     * Mark the given job as failed (or not failed)
     *
     * @param Job  $job
     * @param bool $failed
     */
    public static function setFailed(Job &$job, $failed = true)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_FAILED, $failed);

        return;
    }

    /**
     * Returns true if the given job marked itself as finished.
     *
     * @param Job $job
     *
     * @return boolean
     */
    public static function didFinish(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_FINISHED) !== null;
    }

    /**
     * Returns the time the job marked itself as finished
     *
     * @param Job $job
     *
     * @return DateTimeImmutable
     */
    public static function getFinished(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_FINISHED);
    }

    /**
     * Mark the given job as finished (or not finished)
     *
     * @param Job  $job
     * @param DateTimeImmutable $finished
     */
    public static function setFinished(Job &$job, DateTimeImmutable $finished = null)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_FINISHED, $finished);

        return;
    }

    /**
     * Returns true if the given job marked itself as having a result.
     *
     * @param Job $job
     *
     * @return boolean
     */
    public static function hasResult(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_HAS_RESULT);
    }

    /**
     * Mark the given job as having a result (or not)
     *
     * @param Job  $job
     * @param bool $hasResult
     */
    public static function setHasResult(Job &$job, $hasResult = true)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_HAS_RESULT, $hasResult);

        return;
    }

    /**
     * Returns the result of the job.
     *
     * @param Job $job
     *
     * @return mixed
     */
    public static function getResult(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_RESULT);
    }

    /**
     * Set the result of the given job.
     *
     * @param Job  $job
     * @param bool $result
     */
    public static function setResult(Job &$job, $result = null)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_RESULT, $result);

        return;
    }

    /**
     * Get whether this job is marked as retry-able.
     *
     * @param Job  $job
     *
     * @return bool
     */
    public static function getRetries(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_RETRY);
    }

    /**
     * Set the result of the given job.
     *
     * @param Job $job
     * @param int $retryCount
     */
    public static function setRetryCount(Job &$job, $retryCount)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_RETRY_COUNT, $retryCount);

        return;
    }

    /**
     * Get when this job was last attempted.
     *
     * @param Job $job
     *
     * @return null|DateTimeImmutable
     */
    public static function getLastAttempt(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_LAST_ATTEMPT);
    }

    /**
     * Set when this job was last attempted.
     *
     * @param Job                    $job
     * @param DateTimeImmutable|null $lastAttempt
     */
    public static function setLastAttempt(Job &$job, DateTimeImmutable $lastAttempt = null)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_LAST_ATTEMPT, $lastAttempt);

        return;
    }

    /**
     * Get the progress of this job.
     *
     * @param Job\HasProgress|Job $job
     *
     * @return float
     */
    public static function getProgress(Job\HasProgress &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_PROGRESS);
    }

    /**
     * Set the progress of this job.
     *
     * @param Job\HasProgress|Job $job
     * @param float               $progress
     */
    public static function setProgress(Job &$job, $progress)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_PROGRESS, $progress);

        return;
    }

    /**
     * Get when this job's current attempt will timeout.
     *
     * @param Job $job
     *
     * @return null|DateTimeImmutable
     */
    public static function getTimeout(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_TIMEOUT);
    }

    /**
     * Set when this job's current attempt will timeout.
     *
     * @param Job $job
     * @param DateTimeImmutable|null $timeout
     */
    public static function setTimeout(Job &$job, DateTimeImmutable $timeout = null)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_TIMEOUT, $timeout);

        return;
    }

    /**
     * Get this job's version number.
     *
     * @param Job $job
     *
     * @return int
     */
    public static function getVersion(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_VERSION);
    }

    /**
     * Set this job's version number.
     *
     * @param Job $job
     * @param int $version
     */
    public static function setVersion(Job &$job, $version)
    {
        Reflector::setProperty($job, JobReflector::PROPERTY_VERSION, $version);

        return;
    }
}
