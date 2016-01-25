<?php namespace PHPQ\Driver;

/**
 * A basic in-memory driver intended to be used for testing and development
 * purposes only.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
class ArrayDriver extends AbstractDriver
{
    /**
     * Get the name of this driver.
     *
     * @return string
     */
    public function getName()
    {
        return 'array';
    }
}
