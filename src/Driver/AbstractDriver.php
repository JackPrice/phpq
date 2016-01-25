<?php namespace PHPQ\Driver;

use PHPQ\PHPQ;

/**
 * All PHPQ drivers must concretely extend this class.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
abstract class AbstractDriver
{
    /**
     * The PHPQ instance this driver is attached to.
     *
     * @var PHPQ
     */
    protected $phpq;

    /**
     * Returns the PHPQ instance this driver is attached to.
     *
     * @return PHPQ
     */
    public function getPHPQ()
    {
        return $this->phpq;
    }

    /**
     * Set the PHPQ instance this driver is attached to.
     *
     * @param PHPQ $phpq
     *
     * @return $this
     */
    public function setPHPQ(PHPQ &$phpq)
    {
        $this->phpq = $phpq;

        return $this;
    }

    /**
     * Get the name of this driver.
     *
     * @return string
     */
    abstract public function getName();
}