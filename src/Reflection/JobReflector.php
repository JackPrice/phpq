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
     * Returns true if the given job marked itself as finished.
     *
     * @param Job $job
     *
     * @return boolean
     */
    public static function didFinish(Job &$job)
    {
        return Reflector::getProperty($job, JobReflector::PROPERTY_FINISHED);
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
}
