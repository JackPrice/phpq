<?php namespace PHPQ;

/**
 * This class represents a named job queue - containing any number of jobs.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class Queue
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
}
