<?php namespace PHPQ\Reflection;

use PHPQ\Exception\ReflectionException;
use ReflectionClass;

/**
 * This class contains helper methods for reflecting internally used-classes.
 *
 * @author Jack Price <jackprice@outlook.com>
 */
abstract class Reflector
{
    /**
     * Set the given property on the given object to the value specified.
     *
     * @param $object
     * @param $property
     * @param $value
     *
     * @throws ReflectionException
     */
    public static function setProperty($object, $property, $value)
    {
        $class = new ReflectionClass($object);

        if (!$class->hasProperty($property)) {
            throw new ReflectionException(
                sprintf('Object of class [%s] has no property [%s]', get_class($object), $property)
            );
        }

        $class
            ->getProperty($property)
            ->setValue($object, $value);

        return;
    }

    /**
     * Get the given property on the given object.
     *
     * @param $object
     * @param $property
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function getProperty($object, $property)
    {
        $class = new ReflectionClass($object);

        if (!$class->hasProperty($property)) {
            throw new ReflectionException(
                sprintf('Object of class [%s] has no property [%s]', get_class($object), $property)
            );
        }

        return $class
            ->getProperty($property)
            ->getValue($object);
    }

    /**
     * Mutate the given object to its subclass $target.
     *
     * @param object $object
     * @param string $target
     *
     * @return object
     * @throws ReflectionException
     */
    public static function mutateClass($object, $target)
    {
        if (!is_subclass_of($object, $target)) {
            throw new ReflectionException(
                sprintf('[%s] is not a subclass of [%s]', $target, $object)
            );
        }

        $target = new $target();

        $originalClass = new ReflectionClass($object);
        $targetClass = new ReflectionClass($target);

        foreach ($originalClass->getProperties() as $property) {
            if ($targetClass->hasProperty($property->getName())) {
                $targetClass
                    ->getProperty($property->getName())
                    ->setValue($target, $property->getValue($object));
            }
        }

        return $target;
    }
}
