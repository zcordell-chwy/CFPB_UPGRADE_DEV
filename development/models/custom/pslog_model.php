<?php
/*
 * Author: Ben Johns
 * Project Location: http://http://src.rightnow.com/spaces/logging
 * Blob Hash: $Id$
 * Description: Main Logger class, and supporting class structs.
 * ---------------------------------------------------------------
 * Modified: 20120315 by Andy DUrrans to support 11.2
 */

class pslog_model extends Model
{
    function __construct()
    {
        parent::__construct();
        //This model would be loaded by using $this->load->model('custom/Sample_model');
    }

    function sample_function()
    {
        //This function would be run by using $this->Sample_model->sample_function()

    }
}

class LogException extends \Exception
{

}

class LogTypes
{

    const AddIn = 1;
    const CP = 2;
    const Cron = 3;
    const CustomAPI = 4;
    const Export = 5;
    const ExternalEvent = 6;
    const Import = 7;

    public static $labels = array(
        self::AddIn => 'AddIn',
        self::CP => 'CP',
        self::Cron => 'Cron',
        self::CustomAPI => 'CustomAPI',
        self::Export => 'Export',
        self::ExternalEvent => 'ExternalEvent',
        self::Import => 'Import',
    );

}

class LogSeverities
{

    const Fatal = 1;
    const Error = 2;
    const Warning = 3;
    const Notice = 4;
    const Info = 5;
    const Debug = 6;

    public static $labels = array(
        self::Fatal => 'Fatal',
        self::Error => 'Error',
        self::Warning => 'Warning',
        self::Notice => 'Notice',
        self::Info => 'Info',
        self::Debug => 'Debug',
    );

}

class PSLog
{

    public static $logToFile = false;
    public static $type = null;
    public static $subtype = null;
    public static $severity = null;
    public static $source = null;
    public static $traceThreshold = LogSeverities::Warning;
    public static $interfaceID = null;
    protected static $connectNamespace = 'RightNow\Connect\v1_2';
    protected static $connected = false;
    protected static $logFileDir = "/tmp/PSLog";
    protected static $defaultSeverity = LogSeverities::Error;
    protected static $coreLogFile = null;
    protected static $logFileCache = null;
    protected static $logSchema = array(
        'package' => "PSLog",
        'objects' => array(
            'log' => "Log",
            'severity' => "Severity",
            'type' => "Type",
        ),
    );
    protected static $logClasses = null;

    /**
     * Globally set common properties for logging, so that redundant parameters
     * don't need to be sent in for every createLog() call. May be called any
     * number of times in an execution path.
     *
     * @param int $type
     * @param string $subtype
     * @param int $severity
     * @param string $source
     * @param array $credentials Username and password array for CPHP connection
     */
    public static function init($type, $subtype = null, $severity = null, $logToFile = false, $credentials = null, $source = null)
    {
        // Initialize core log file for CPHP and CBO errors
        self::$coreLogFile = self::initLogFile(self::$logFileDir);

        try
        {
            self::$type = $type;
            self::$subtype = $subtype;
            self::$severity = $severity;
            self::$logToFile = $logToFile;
            self::$source = $source;
            self::$interfaceID = intf_id();
            if ($credentials !== null)
            {
                if (!is_array($credentials))
                {
                    $e = new \LogException("\$credentials parameter must be an array of username and password.");
                    self::addCoreFileEntry($e, LogSeverities::Warning);
                }
                elseif (isset($credentials[0]) && isset($credentials[1]))
                {
                    self::connect($credentials[0], $credentials[1]);
                }
            }
        }
        catch (\Exception $e)
        {
            self::addCoreFileEntry($e, LogSeverities::Fatal);
        }
    }

    /**
     * Manually connect to Connect for PHP API with optional login credentials.
     *
     * @param string $username Valid account username with which to connect
     * @param string $password Valid account password with which to connect
     */
    public static function connect($username = null, $password = null)
    {
        try
        {
            // Load ConnectPHP init file
            $connectFilepath = get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph'
            if (!file_exists($connectFilepath))
            {
                throw new \LogException(sprintf("ConnectPHP initialization file '%s' could not be found (or could not be read).", $connectFilepath));
            }
            require_once($connectFilepath);

            // Optionally use login credentials
            if ($username !== null && $password !== null)
            {
                initConnectAPI(strval($username), strval($password));
            }
            else
            {
                initConnectAPI();
            }
            self::$connected = true;
        }
        catch (\Exception $e)
        {
            self::addCoreFileEntry($e, LogSeverities::Fatal);
            return false;
        }
        return true;
    }

    public static function create($message, $severity = null, $references = null, $note = null, $subtype = null, $type = null, $source = null)
    {
        try
        {
            if (self::$logToFile)
            {
                // Get log directory and file to write to
                $dir = self::getLogDir($type, $subtype);
                if (isset(self::$logFileCache[$dir]))
                {
                    $filename = self::$logFileCache[$dir];
                } else {
                    $filename = self::initLogFile(self::$logFileDir . "/" . $dir);
                    self::$logFileCache[$dir] = $filename;
                }

                return self::addFileEntry($filename, $message, $severity, $note, $subtype, $type, $source);
            }
            else
            {
                if (!self::$connected)
                {
                    self::connect();
                }
                if (self::$logClasses === null)
                {
                    self::generateClasses(self::$logSchema);
                }
                return self::addEntry($message, $severity, $references, $note, $subtype, $type, $source);
            }
        }
        catch (\Exception $e)
        {
            self::addCoreFileEntry($e, self::$defaultSeverity);
            return false;
        }
    }

    /**
     * Creates log directory if needed, and sets logFile property.
     *
     * @param string $logDir Log directory to initialize.
     * @param bool $clearLog Whether to clear logfile.
     * @return string Path to current log file.
     */
    protected static function initLogFile($logDir, $clearLog = false)
    {
        // Create log directory if it doesn't exist
        if (!is_dir($logDir))
        {
            $oldumask = umask(0);
            mkdir($logDir, 0775, true);
            umask($oldumask);
            if (!is_dir($logDir))
            {
                throw new \LogException(sprintf("Log directory could not be created: %s", $logDir));
            }
        }

        // Generate log filename based on date
        $logFile = sprintf("%s/%s.log", $logDir, date('Y-m-d'));
        if ($clearLog)
        {
            file_put_contents($logFile, "");
        }
        return $logFile;
    }

    protected static function addFileEntry($filename, $message, $severity, $note, $subtype, $type, $source)
    {
        // Sanity checks
        if ($severity == null)
        {
            $severity = self::$severity;
            if ($severity === null)
            {
                $severity = self::$defaultSeverity;
            }
        }
        if ($source == null)
        {
            $source = self::$source;
        }

        // Instantiate trace and source, and get exception message if needed
        $source = self::getSource();
        list($trace, $messageStr) = self::getTraceAndMessage($message, $severity);

        // Set log object properties
        if ($note !== null)
        {
            $note = sprintf("\nNote: %s", $note);
        }
        if ($source !== null)
        {
            $source = sprintf("\nSource: %s", $source);
        }
        if ($trace !== null)
        {
            $trace = sprintf("\nTrace: %s", $trace);
        }

        $output = sprintf("%s (%s): %s%s%s%s\n\n", date('c'), LogSeverities::$labels[$severity], $messageStr, $source, $note, $trace);
        file_put_contents($filename, $output, FILE_APPEND);
    }

    protected static function getTraceAndMessage($message, $severity)
    {
        if (is_a($message, 'Exception'))
        {
            // Dump entire exception as trace
            $trace = $message->__toString();
            $messageStr = $message->getMessage();
        }
        else
        {
            // Generate trace if severity is higher than trace threshold
            if ($severity <= self::$traceThreshold)
            {
                $e = new Exception;
                $trace = sprintf("Auto-generated by PSLog:\n%s", $e->getTraceAsString());
            }
            else
            {
                $trace = null;
            }
            $messageStr = strval($message);
        }
        return array($trace, $messageStr);
    }

    protected static function getLogDir($type, $subtype)
    {
        // Organize log files in directories named subtype_type
        $dir = null;

        // Add type string to directory name
        if ($type == null)
        {
            if (self::$type == null)
            {
                throw new \LogException("Log type not defined. Must call PSLog::init() or explicitly set type when calling PSLog::create().");
            }
            else
            {
                $type = self::$type;
            }
        }

        // Add subtype string to directory name
        if ($subtype == null)
        {
            $subtype = self::$subtype;
        }

        // Create directory name
        if (is_string($subtype))
        {
            $dir = sprintf("%s_%s", self::alphaNumStr($subtype), LogTypes::$labels[$type]);
        } else {
            $dir = LogTypes::$labels[$type];
        }

        // Check for valid log directory
        if ($dir == null || $dir == "")
        {
            throw new \LogException("Could not generate custom log directory.");
        }

        return $dir;
    }

    protected static function alphaNumStr($str)
    {
        if (is_string($str) || $str !== "")
        {
            return preg_replace("/[^a-zA-Z0-9]/", "", $str);
        }
    }

    protected static function addCoreFileEntry($e, $severity = null)
    {
        if ($severity == null)
        {
            $severity = self::$defaultSeverity;
        }
        if (self::$coreLogFile === null)
        {
            self::$coreLogFile = self::initLogFile(self::$logFileDir);
        }
        $output = sprintf("%s (%s): %s\n\n", date('c'), LogSeverities::$labels[$severity], strval($e));
        file_put_contents(self::$coreLogFile, $output, FILE_APPEND);
    }

    protected static function generateClasses($schema)
    {
        // Sanity checks
        if (!is_array($schema))
        {
            throw new \LogException("Log schema must be an array.");
        }
        if (!isset($schema['package']))
        {
            throw new \LogException("Package name must exist in log schema.");
        }
        if (!isset($schema['objects']) || !is_array($schema['objects']))
        {
            throw new \LogException("Log schema must contain an array of object classnames.");
        }

        // Generate namespace/class string from object names
        self::$logClasses = array();
        foreach ($schema['objects'] as $desc => $object)
        {
            $class = sprintf("%s\%s\%s", self::$connectNamespace, $schema['package'], strval($object));
            if (!class_exists($class))
            {
                throw new \LogException(sprintf("Class '%s' does not exist.", $class));
            }
            self::$logClasses[$desc] = sprintf("\%s", $class);
        }
    }

    protected static function addEntry($message, $severity, $references, $note, $subtype, $type, $source)
    {
        // Sanity checks
        if (!isset(self::$logClasses['log']) && !isset(self::$logClasses['severity']) && !isset(self::$logClasses['type']))
        {
            throw new \LogException("Log classes were not instantiated from schema correctly.");
        }
        if ($type == null)
        {
            if (self::$type == null)
            {
                throw new \LogException("Log type not defined. Must call PSLog::init() or explicitly set type when calling PSLog::create().");
            }
            else
            {
                $type = self::$type;
            }
        }
        if ($subtype == null)
        {
            $subtype = self::$subtype;
        }
        if ($severity == null)
        {
            $severity = self::$severity;
        }
        if ($source == null)
        {
            $source = self::$source;
        }

        // Instantiate log object
        $logClass = self::$logClasses['log'];
        $entryObj = new $logClass();

        // Instantiate type object
        $typeClass = self::$logClasses['type'];
        $typeObj = $typeClass::fetch($type);

        // Set default severity and instantiate object
        if ($severity === null)
        {
            $severity = self::$defaultSeverity;
        }
        $severityClass = self::$logClasses['severity'];
        $severityObj = $severityClass::fetch($severity);

        // Instantiate trace and source, and get exception message if needed
        $source = self::getSource();
        list($trace, $messageStr) = self::getTraceAndMessage($message, $severity);

        // Set log object properties
        $entryObj->Type = $typeObj;
        $entryObj->Message = $messageStr;
        $entryObj->Severity = $severityObj;
        if ($subtype !== null)
        {
            $entryObj->SubType = strval($subtype);
        }
        if ($note !== null)
        {
            $entryObj->Note = strval($note);
        }
        if ($source !== null)
        {
            $entryObj->Source = strval($source);
        }
        if ($trace !== null)
        {
            $entryObj->StackTrace = substr(strval($trace), 0,4000);
        }
        if ($references !== null)
        {
            self::addReferences($entryObj, $references);
        }
        if (self::$interfaceID > 0)
        {
            $entryObj->Interface = self::$interfaceID;
        }
        $result = $entryObj->save();

        return $result;
    }

    protected static function getSource()
    {
        foreach (debug_backtrace(false) as $item)
        {
            if ($item['file'] !== __FILE__)
            {
                return substr($item['file'], -255);
            }
        }
    }

    protected static function addReferences(&$entryObj, $references)
    {
        // Add array of refrerences, or single reference object
        if (is_array($references))
        {
            foreach ($references as $reference)
            {
                try
                {
                    self::addReferenceObj($entryObj, $reference);
                }
                catch (\Exception $e)
                {
                    self::addCoreFileEntry($e, LogSeverities::Warning);
                }
            }
        }
        else
        {
            try
            {
                self::addReferenceObj($entryObj, $references);
            }
            catch (\Exception $e)
            {
                self::addCoreFileEntry($e, LogSeverities::Warning);
            }
        }
    }

    protected static function addReferenceObj(&$entryObj, $reference)
    {
        if (!is_object($reference))
        {
            throw new \LogException(sprintf("Reference is not an object: %s", print_r($reference, true)));
        }
        $refClass = get_class($reference);
        $meta = $entryObj::getMetaData();
        $found = false;
        foreach ($meta as $property)
        {
            if (is_object($property)
                && $refClass == $property->type_name
                && $property->name != "Updated_ByAccount"
                && $property->name != "Created_ByAccount")
            {
                $propName = $property->name;
                $entryObj->$propName = $reference;
                $found = true;
            }
        }
        if (!$found)
        {
            throw new \LogException(sprintf("Reference could not be found on log object: %s", print_r($reference, true)));
        }
    }

    /**
     * Returns an array of all object properties (excluding '_metadata' property).
     * Useful for inspecting objects returned from ROQL queries and Connect
     * for PHP methods. This WILL trigger the lazy loading of lookup objects and
     * traverse them when $recursive is true.
     * @throws RightNow\Connect\v1_2\ConnectAPIErrorBase
     * @param mixed $object         Any object
     * @param bool $recursive       Optional: recursively get properties
     *                              from nested objects
     * @return mixed                Array of object properties, or single property
     */
    public static function dump($object, $recursive = false)
    {
        // If property is object, recursively get sub properties
        if (is_object($object))
        {
            $outputArray = array();
            // Use reflection to get public and dynamic properties
            $reflect = new \ReflectionObject($object);
            $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
            // Loop through property list
            foreach ($props as $prop)
            {
                $propName = $prop->getName();
                // Exclude metadata from properties list
                if ($propName != "_metadata")
                {
                    if ($recursive)
                    {
                        $outputArray[$propName] = self::dump($object->$propName, $recursive);
                    }
                    else
                    {
                        $outputArray[$propName] = $object->$propName;
                    }
                }
            }
            return $outputArray;
        }
        // If property is array, recursively get properties of elements
        elseif (is_array($object))
        {
            $subArray = array();
            foreach ($object as $idx => $propElem)
            {
                $subArray[$idx] = self::dump($propElem, $recursive);
            }
            return $subArray;
        }
        // Non-object or non-array property
        else
        {
            return $object;
        }
    }

}
