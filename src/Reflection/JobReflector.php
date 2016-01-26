<?php namespace PHPQ\Reflection;

use DateTime;
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
        return Reflector::getProperty($job, JobReflector::PROPERTY_FINISHED);
    }

    /**
     * Mark the given job as finished (or not finished)
     *
     * @param Job  $job
     * @param bool $finished
     */
    public static function setFinished(Job &$job, $finished = true)
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
     * Convert the given job to JSON.
     *
     * @param Job $job
     *
     * @return string
     */
    public static function toJSON(Job &$job)
    {
        return json_encode(array(
            '__CLASS__' => get_class($job),
            'created' => JobReflector::getCreated($job)->getTimestamp(),
            'schedule' => JobReflector::getSchedule($job)->getTimestamp(),
            'parameters' => JobReflector::getParameters($job),
        ));
    }

    /**
     * Convert json to a job.
     *
     * @param $json
     *
     * @return Job
     */
    public static function fromJSON($json)
    {
        $data = json_decode($json, true);

        $class = $data['__CLASS__'];

        $job = new $class();

        JobReflector::setCreated($job,
            (new DateTimeImmutable())->setTimestamp($data['created'])
        );
        JobReflector::setSchedule($job,
            (new DateTimeImmutable())->setTimestamp($data['schedule'])
        );
        if (is_array($data['parameters'])) {
            JobReflector::setParameters($job, $data['parameters']);
        }

        return $job;
    }
}
