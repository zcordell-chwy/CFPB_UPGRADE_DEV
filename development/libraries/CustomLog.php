<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Author: Ben Johns 
 * Project Location: http://http://src.rightnow.com/spaces/logging
 * Blob Hash: $Id: a27453296399dd62b3dd8f1e18182e7fdff0d5ac $
 * Description: CP proxy for PSLog class. Allow CI to load the PSLog class as
 *              a library, and wraps all static methods and properties in
 *              methods that allow them to be accessed via an object context.
 *              (e.g. rather than calling PSLog::init() you can call
 *                    $CI->customlog->init()
 */

define("CUSTOM_SCRIPT", true);
if (!class_exists("PSLog"))
{
    require_once("custom/src/oracle/libraries/PSLog-1.1.phph");
}

use PS\Log\v1_1\Log;
use PS\Log\v1_1\Type;
use PS\Log\v1_1\Severity;

class CustomLog
{    
    /**
     * Dynamically call all object context methods, using __call() magic
     * method. All methods of PSLog should be static, so allow
     * CI object context methods to be used in a static context.
     * 
     * @param string $name Name of method to call
     * @param mixed $arguments Array of arguments
     */
    public function __call($name, $arguments)
    {
        return self::__callStatic($name, $arguments);
    }
    
    /**
     * Dynamically call all static context methods, using __callStatic() magic
     * method
     * 
     * @param string $name Name of static method to call
     * @param mixed $arguments Array of arguments
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(sprintf("Log::%s", $name), $arguments);
    }
    
    /**
     * Dynamically get all object context properties, using __get() magic 
     * method. All properties of PSLog should be static, so allow
     * CI object context properties to be used in a static context.
     * 
     * @param string $name
     * @return mixed 
     */
    public function __get($name)
    {
        return PSLog::$$name;
    }
    
    /**
     * Dynamically set all object context properties, using __set() magic 
     * method. All properties of PSLog should be static, so allow
     * CI object context properties to be used in a static context.
     * 
     * @param string $name
     * @param mixed $value 
     */
    public function __set($name, $value)
    {
        PSLog::$$name = $value;
    }
}
