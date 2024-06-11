<?php

namespace Sloth\Core;
/**
 * Class Singleton
 *
 * a simple implementation for Singleton classes
 * Classes that extend this class, can be used as a Singleton
 */
class Singleton
{
    private static array $instances = [];

    /**
     * Singleton constructor.
     *
     * protected so it can't be called outside of the class
     */
    protected function __construct()
    {
    }

    /**
     * return an instance of the called class
     *
     * @return mixed|\static
     */
    public static function getInstance()
    {

        // late-static-bound class name
        $classname = get_called_class();
        if (!isset(self::$instances[$classname])) {
            self::$instances[$classname] = new static;
        }

        return self::$instances[$classname];
    }
}
