<?php
/**
 * Allgemeine Logging Klasse. Diese Klasse kann ein Errorhandler instanzieren, <br>
 * dammit können Messages unabhängig in dieser Klasse gespeichert werden.
 * 
 * @author 		Walter Gyr GYW		WACOSOFT
 * 
 * @package 	base
 */

 /**
 *
 * @author 		Walter Gyr (GYW)
 * @version 	Revision: 1.2
 */
define("ERROR", E_USER_ERROR);
define("WARNING", E_USER_WARNING);
define("INFO", E_USER_NOTICE);
define("ALL_ERROR", E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR );
define("STATUSBAR", 9901);


define("EX_FATALERROR", 9999); 

define("DUMP", "DUMP_THIS_VALUE");
define("DUMP_NOTICE", "DUMP_THIS_NOTICE");

function trigger_exception($exception)
{
	GLOBAL $ErrorLogger;
	if ( isset($ErrorLogger) && is_object($ErrorLogger) )
	{
		$ErrorLogger->exceptionHandler($exception);
	}
}

/** @var Logger $ErrorLogger set by error handler */
$ErrorLogger = null;

/**
 * Exception class for the logger extends Exception with error information.
 *
 * @package     base
 */
class LoggerException extends Exception
{
	/**
	 * context, which is an array that points to the active symbol table at the point the error occurred. 
	 * In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in. 
	 * User error handler must not modify error context. 
	 *
	 * @var array
	 */
	private $context = null;
	
	
    /**
     * Extends the standard exception with error information
     *
     * @param string $message		contains the error message, as a string. 
     * @param int $code				contains the level of the error raised, as an integer. 
     * @param string $file			which contains the filename that the error was raised in, as a string. 
     * @param int $line				which contains the line number the error was raised at, as an integer. 
     * @param array $context		which is an array that points to the active symbol table at the point the error occurred. 
     */
    public function __construct($message, $code, $file, $line, $context) 
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }
    
    /**
     * Return the context array
     *
     * @return array
     */
    final function getContext()
    {
    	return $this->context;
    }
}

/** 
*	This class manage the logging function to file, sdtout or buffer
*	     
* 	16.09.2004  create  
*	07.12.2004  change php5  
*    
* @author 	Walter Gyr      GYW
* @version 	Revision: 1.2
* @package 	base
*/
class Logger
{
    /** Verwendung des Standard Encoding */
    const OUT_ENCODING_STD                      = 0;
    /** Verwendung von UTF-8 Encoding */
    const OUT_ENCODING_UTF8_ENC                 = 1;
    /** Verwendung von UTF-8 Decoding */
    const OUT_ENCODING_UTF8_DEC                 = 2;
    /** Ausgabe eines Dump über Messagehandler */
    const DUMP                                  = 9902;
    /** Ausgabe einer Message über Messagehandler */
    const MSG                                   = 9903;


	private   		$activeErrorHandler 		= FALSE;
	private   		$activeAssertHandler 		= FALSE;
	private   		$activeExceptionHandler 	= FALSE;
	private 		$oldErrHandler				= NULL;
    /** @var null|resource $log */
	private 		$log 						= NULL;
	private 		$fileName 					= "";
	private 		$logPath 					= "";
	private 		$logName 					= "";
	private 		$openTyp 					= "w";
	private 		$buffer 					= "";
	private 		$seekPos 					= 0;
	private 		$ERROR_PREFIX 				= "ERROR : ";
	private 		$WARNING_PREFIX 			= "WARNING : ";
	private 		$INFO_PREFIX    			= "INFO : ";
	private 		$append 					= FALSE;
 	private 		$readWrite 					= FALSE;
 	private 		$useStdout 					= FALSE;
  	private 		$mode 						= FALSE;
  	private 		$eachWrite 					= FALSE;
  	private 		$forceOpen 					= FALSE;
  	private 		$activeShutDownHandler		= FALSE;
  	private 		$infoOutputFilter			= TRUE;
  	private 		$warningOutputFilter		= TRUE;
  	private 		$dumpAtError				= FALSE;
  	private 		$errorCounter 				= 0;
  	private 		$errorToStdout				= FALSE;
  	private 		$warningToStdout			= FALSE;
  	private 		$infoToStdout				= FALSE;
  	private 		$noInfo						= FALSE;
    /** @var null|callable  $errorMsgHandler */
  	private 		$errorMsgHandler			= null;
    /** @var null|callable  $warningMsgHandler */
  	private 		$warningMsgHandler			= null;
    /** @var null|callable  $infoMsgHandler */
  	private 		$infoMsgHandler				= null;
    /** @var null|array $shutDownCallback */
  	private 		$shutDownCallback			= null;
  	private 		$outputEncoding				= self::OUT_ENCODING_STD;
  	private 		$exceptionMode				= 0;
  	
  	public  		$importCounter				= 0;		// ZEND stuff  
  	static public   $XML2PHP 					= array(0=>0,1=>E_USER_NOTICE,2=>E_USER_WARNING,3=>E_USER_ERROR);
    private         $oldExceptionHandler        = null;
	public			$stdOutFunction				= null;


    /**
     *    The class Logger can log text to a file, stdout or buffer<br>
     *    After this call the logfile is open except the eachWrite is on
     *
     *     __constructor
     *
     * @param string    $filename     ""  => logging to stdout
     *                                        "c:/temp/myLofgile.log" => complete filepath
     * @param boolean    $mode       TRUE => with buffer, FALSE => to file      (default FALSE)
     * @param boolean    $append     TRUE => open file in append mode       (default FALSE)
     * @param boolean    $readWrite  TRUE => open file in read,write mode (default FALSE)
     * @param boolean    $eachWrite  TRUE => each write function open and close the logfile
     * @param boolean    $noInfo     TRUE => do not write info messages
     */
 	public function __construct($filename="", $mode=FALSE, $append=FALSE, $readWrite=FALSE, $eachWrite=FALSE, $noInfo=FALSE)
	{
		$this->noInfo = $noInfo;
	   	$this->mode = $mode;
		if (strlen($filename) == 0)
		{
			$this->useStdout = TRUE;
		}
		else
		{
			$this->useStdout = FALSE;
			$this->fileName = $filename;
			$this->logPath = dirname($filename);
			$this->logName = basename($filename);
			$this->append = $append;
			$this->readWrite = $readWrite;
			$this->eachWrite = $eachWrite;
			if ($this->append) $this->openTyp = "a";
			if ($this->readWrite) $this->openTyp .= "+";
		}
		$this->open();
	}
	/**
	*	Destructor close a open logger
	*
	* @return void
	*/
	public function __destruct()
	{
		Global $ErrorLogger;
		
		if ($this->activeErrorHandler)
		{
			$ErrorLogger = NULL;
			restore_error_handler();
			$this->activeErrorHandler = FALSE;
		}
		if ($this->activeExceptionHandler)
		{
			restore_exception_handler();
			$this->activeExceptionHandler = FALSE;
		}
		if ($this->activeAssertHandler)
		{
			assert_options (ASSERT_ACTIVE, 1);
			$this->activeAssertHandler = FALSE;
		}
		$this->close();
	}
	/**
	*	Dump data to the default logger
	*
	* @return void
	* @param all	mixed		dump each type 
	* @param string	$note		title of data
	*/
	static function dbgDump($mixed, $note="")
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->dump($mixed, $note);
		}
	}

    /**
     *    Dump string to the default logger
     *
     * @param string    $str
     * @param bool      $addBR
     * @param bool      $toStdOutput
     */
	static function dbgMsg($str, $addBR=TRUE, $toStdOutput=FALSE)
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->msg($str, $addBR);
			
			if ($toStdOutput)
			{
				if ($addBR) $str .= "\n";
				if (is_null($ErrorLogger->stdOutFunction))
				{
					print($str);
				}
				else
				{
					print(call_user_func($ErrorLogger->stdOutFunction, $str));
				}
			}
		}	
	}
	/**
	*	Dump time + string to the default logger
	*
	* @param string		$str		
	* @return void
	*/
	static function dbgTimeMsg($str)
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->timeMsg($str);
		}
	}
	/**
	*	Flush lofile on the default logger
	*
	* @return void
	*/
	static function dbgFlush()
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->flush();
		}
	}
	/**
	* Dump the stack trace to the default logger
	*
	* @return void
	* @param string 	$ident	=> description in the log
	* @param boolean 	$full	=> dump full string, array and object parameter
	*/
	static function dbgTrace($ident="setBackTrace", $full=FALSE)
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->setBackTrace($ident, $full);
		}
	}
	/**
	* Return act date and message (if set) '31.12.2004 23:55.30 .............  ' 
	*
	* @param string $msg		=> message
	* @param string $format		=> time format see date php doc
	* @return string
	*/
	static function getTimeString($msg=NULL, $format="d.m.Y h:i.s")
	{
		return trim(sprintf("%s %s", date($format), $msg));
	}
	/**
	*	Close the open default error logger
	*
	* @return void
	*/
	static function closeErrorLogger()
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->close();
		}
	}
	/**
	 * Enter description here...
	 *
	 * @return integer
	 */
	static function getDefaultErrorCounter()
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			return $ErrorLogger->getErrorCounter();
		}
		return NULL;
	}
	/**
	*	Setting the asserthandler in this class
	*
	* @return boolean
	*/
	public function setAssertHandler()
	{
		if ($this->activeAssertHandler) return FALSE;
		assert_options (ASSERT_ACTIVE, 1);
		assert_options (ASSERT_WARNING, 0);
		assert_options (ASSERT_QUIET_EVAL, 1);
		assert_options (ASSERT_CALLBACK, array($this, "assertHandler"));
		$this->activeAssertHandler = TRUE;
		return TRUE;
	}
	/**
	*
	* Set new PHP assert handler to this logger class
	*
	* @return void
	* @param string		$file
	* @param integer	$line
	* @param string		$code
	*/
	public function assertHandler($file, $line, $code)
	{
		$this->msg(sprintf("ASSERT : File = %s, Line = %d, Code = %s", $file, $line, $code));
		
	}
	/**
	*	Setting the exceptionhandler in this class
	*
	* @return boolean
	*/
	public function setExceptionHandler()
	{
		if ($this->activeExceptionHandler) return FALSE;
		$this->oldExceptionHandler = set_exception_handler(array($this, "exceptionHandler"));
		$this->activeExceptionHandler = TRUE;
		return FALSE;
	}
	/**
	*
	* Set new PHP exception handler to this logger class
	*
	* @return void
	* @param object		$exception
	*/
	public function exceptionHandler($exception)
	{
		$this->msg(sprintf("EXCEPTION : File = %s, Line = %d, Message = %s, Code = %d\nTRACE : %s", $exception->getFile(), $exception->getLine(), $exception->getMessage(), $exception->getCode(), $exception->getTraceAsString()));
	}
	/**
	*	Setting the errorhandler in this class
	*
	* @param  boolean	$dumpAtError	=> Dump position and context values by errors (development)
	* @return boolean
	*/
	public function setErrorHandler($dumpAtError=TRUE)
	{
		Global $ErrorLogger;
		
		if ($this->activeErrorHandler) return FALSE;
		$this->oldErrHandler = set_error_handler(array($this, "errorHandler"));
		$this->activeErrorHandler = TRUE;
		$this->dumpAtError = $dumpAtError;
		$ErrorLogger = $this;
		ini_set("html_errors", 0);
		error_reporting(E_ALL);
		return TRUE;
	}
	/**
	*	Setting the error exception handler in this class
	*
	* @param  integer	$exceptionMode  Error level see php predefined constants
	* @return boolean
	*/
	public function setErrorExceptionHandler($exceptionMode=ALL_ERROR)
	{
		Global $ErrorLogger;
		
		if ($this->activeErrorHandler) return FALSE;
		$this->oldErrHandler = set_error_handler(array($this, "errorHandlerException"));
		$this->exceptionMode = $exceptionMode;
		$this->activeErrorHandler = TRUE;
		$ErrorLogger = $this;
		ini_set("html_errors", 0);
		error_reporting(E_ALL);
		return TRUE;
	}
	/**
	 *  Set new value for to exceptionMode
	 *
	 * @param  integer	$exceptionMode  Error level see php predefined constants
	 * @return integer  old $exceptionMode
	 */
	public static function setErrorExceptionMode($exceptionMode)
	{
		Global $ErrorLogger;
		
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$old = $ErrorLogger->exceptionMode;
			$ErrorLogger->exceptionMode = $exceptionMode;
			return $old;
		}
		return null;
	}

    /**
     *    Setting shutdown handler
     *
     * @return boolean
     */
	public function setShutDownHandler()
	{
		if ($this->activeShutDownHandler) return FALSE;
		register_shutdown_function (array($this, "shutDownHandler"));
		$this->activeShutDownHandler = TRUE;
		return TRUE;
	}
	/**
	*
	* Set new PHP shut down handler to this logger class
	*
	* @return void
	*/
	public function shutDownHandler()
	{
		$state = connection_status();
		if ($state == 2)
		{
			$this->errorMsg("Abbruch durch Verbindungs Timeout");
		}
		else if ($state == 1)
		{
			$this->errorMsg("Abbruch durch Benutzer");
		}
        //test error
        $error = error_get_last();
        if (is_array($error))
        {
            $this->errorHandler($error['type'], $error['message'], $error['file'], $error['line'], "Shutdown");
        }
		if (is_array($this->shutDownCallback) && count($this->shutDownCallback) > 0)
		{
			foreach ($this->shutDownCallback as $arguments) 
			{
            	$callback = array_shift($arguments);
            	call_user_func_array($callback, $arguments);
        	}
		}
	}

    /**
     * Set shutdown function to the shutdown handler
     * Each registered shutdown function are called when the script shutdown.
     *
     * @return boolean
     */
	public function registerShutDownFunction()
	{
        /** @var $callback array */
		$callback = func_get_args();
        if (empty($callback)) {
            $this->errorMsg('Keine Parameter übergabe '.__FUNCTION__.' Funktion');
            return false;
        }
        if (!is_callable($callback[0])) {
            trigger_error('Parameter keine ausführbare Funktion in '.__FUNCTION__);
            return false;
        }
        $this->shutDownCallback[] = $callback;
        return true;
		
	}
	/**
	*
	* Set new PHP error handler to a logger class
	*
	* @return void
	* @param integer	$errNo
	* @param string		$errStr
	* @param string		$errFile
	* @param integer	$errLine
	* @param mixed		$errContext
	*/
	public function errorHandler($errNo, $errStr, $errFile, $errLine, $errContext)
	{
        if (($errNo & error_reporting()) == 0) return;
        $errFile = str_replace("\\", "/", $errFile);
        $errStr = mb_convert_encoding($errStr, "UTF-8");
		switch($errNo)
		{
			case E_ERROR 	:
			case E_PARSE	:	$this->msg("E_ERROR : ".$errStr);
								$this->msg(sprintf("E_ERROR : FILE = %s, LINE = %d", $errFile, $errLine));
								$this->dump($errContext, "At error position");
								break;
			case E_WARNING	:	$this->msg(sprintf("E_WARNING : %s, FILE = %s, LINE = %d", $errStr, $errFile, $errLine));
								break;
			case E_NOTICE	:	$this->msg(sprintf("E_NOTICE :  %s, FILE = %s, LINE = %d", $errStr, $errFile, $errLine));
								break;
			case ERROR		:	$this->errorMsg(sprintf("%s, FILE = %s, LINE = %d", $errStr, $errFile, $errLine));
								if ($this->dumpAtError)
								{
									$this->errorMsg(sprintf("FILE : %s, LINE : %s", $errFile, $errLine));
									$this->dump($errContext, "At error position");
								}
								break;
			case WARNING	:	$this->warnMsg(sprintf("%s, FILE = %s, LINE = %d", $errStr, $errFile, $errLine));
								break;
								
			case INFO		:	if (is_array($errStr) && array_key_exists(DUMP, $errStr))
								{
									if ( array_key_exists(DUMP_NOTICE, $errStr) )
									{
										$this->dump($errStr[DUMP], $errStr[DUMP_NOTICE]);
									}
									else 
									{
										$this->dump($errStr[DUMP]);
									}
								}
								else if (!$this->noInfo)
								{
									$this->infoMsg($errStr);
								}
								break;
			default			:	$this->warnMsg(sprintf("UNKOWN PHP Message %s, Type : %d, FILE : %s, LINE : %s", $errStr, $errNo, $errFile, $errLine));		
								break;	
		}
		//return false;
	}

    /**
     *
     * Set new PHP error handler to throws exception on a error
     *
     * @param integer    $errNo
     * @param string     $errStr
     * @param string     $errFile
     * @param integer    $errLine
     * @param mixed      $errContext
     * @throws LoggerException
     * @return boolean
     */
	public function errorHandlerException($errNo, $errStr, $errFile, $errLine, $errContext)
	{ 
		$this->errorHandler($errNo, $errStr, $errFile, $errLine, $errContext);
		if (($errNo & $this->exceptionMode) > 0)
		{
			throw new LoggerException( $errStr, $errNo, $errFile, $errLine, $errContext);
		}
		return true;
	}
	/**
	* Dump the stack trace to the logger
	*
	* @return void
	* @param string 	$ident	=> description in the log
	* @param boolean 	$full	=> dump full string, array and object parameter
	*/
	public function setBackTrace($ident="setBackTrace", $full=FALSE)
	{
		$this->msg("STACK : $ident");
		$backtrace = debug_backtrace();
		$level = -1;
		foreach ($backtrace as $bt) 
		{
			$level++;
			//first level is function setBackTrace
			if ($level == 0) continue;
	       	$args = '';
	       	$p = 0;
	       	foreach ($bt['args'] as $a) 
			{
	     		$p++;
				if (!empty($args)) {
				   $args .= ', ';
				}
				switch (gettype($a)) 
				{
				case 'integer':
				case 'double':
				   $args .= $a;
				   break;
				case 'string':
						if (!$full)
						{
				   			$a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
						}
				   $args .= "\"$a\"";
				   break;
				case 'array':
				   $args .= 'Array('.count($a).')';
				   if ($full)
				   {
				   		$this->dump($a, "Array parameter $p of function : ".$bt['function']);
				   }
				   break;
				case 'object':
				   $args .= 'Object('.get_class($a).')';
				   if ($full)
				   {
				   		$this->dump($a, "Object parameter $p of function : ".$bt['function']);
				   }
				   break;
				case 'resource':
				   $args .= 'Resource('.strstr($a, '#').')';
				   break;
				case 'boolean':
				   $args .= $a ? 'True' : 'False';
				   break;
				case 'NULL':
				   $args .= 'Null';
				   break;
				default:
				   $args .= 'Unknown';
				}
			}
			$class = "";
			$type = "";
			if (isset($bt['class']) ) $class = $bt['class'];
			if (isset($bt['type']) ) $type = $bt['type'];
			$this->msg(sprintf("STACK : Call = %s %s %s (%s),   Line = %d, File = %s", $class, $type, $bt['function'], $args, $bt['line'], $bt['file']));
	   }
	}
	/**
	* @return string
	* @desc get log path from logfile
	*/
	public function getLogPath()
	{
		return $this->logPath;
	}
	/** 
	*	Open the logger by mode
	*
	*	@param	boolean	$forceOpen  : TRUE overwrite the eachWrite flag	
	*	@return boolean	
	*/  
	public function open($forceOpen=FALSE)
	{   
		if ($this->mode) // Buffer mode
		{
			$this->buffer = "";
		}
		else
		{
			if (strlen($this->fileName) == 0)
			{
				$this->log = nfopen('php://stdout', 'w');
			}
			else if (!$this->eachWrite || $forceOpen)
			{
				$this->forceOpen = $forceOpen;
				$this->log = nfopen($this->fileName, $this->openTyp);
			}
		}
		return $this->getState();
	}

    /**
     *    Close logger for each mode
     *    Buffer    mode : Open logfile and write buffer to file and close <br>
     *    File    mode : Close logfile if open (eachWrite) <br>
     *    Stdout    mode : Close stdout pipe
     *
     * @param bool $delete
     */
	public function close($delete=FALSE)
	{
		if ($this->mode) // Buffer mode
		{
			if (strlen($this->buffer) > 0)
			{
				if (!$delete)
				{
					$this->mode = FALSE;
					if ($this->open())
					{
						$this->msg($this->buffer);
						if ($this->log) // in eachWrite mode is closed
						{
							fclose($this->log);
						}
						$this->log = NULL;
						$this->buffer = "";
					}
					$this->mode = TRUE;
				}
			}
		}
		else if ($this->log)
		{
            fclose($this->log);
			$this->log = NULL;
			$this->forceOpen = FALSE;
			if ($delete)
			{
				nunlink($this->fileName);
			}
		}
	}
	/** 
	*	Get state for each mode
	*	Buffer 	mode : 	always 					=>	TRUE <br>
	*	File 	mode : 	eachWrite 				=> 	TRUE else if logfile open         =>  TRUE <br>
	*	Stdout 	mode :  if stdout pipe open     =>  TRUE <br>
	*
	*	@return	boolean
	*/
	public function getState()
	{
		if ($this->mode) return TRUE;
		if ($this->eachWrite && !$this->forceOpen) return TRUE;
		if ($this->log) return TRUE;
		return FALSE;
	}
	/** 
	*	Get buffer for each mode 
	*	Buffer 	mode : 	return the buffer <br>
	*	File 	mode : 	return FALSE <br>
	*	Stdout 	mode :  return FALSE <br>
	*
	*	@return	string
	*/
	public function getBuffer()
	{
		if ($this->mode) return $this->buffer;
		return FALSE;
	}

    /**
     * Put the string $msg + linefeed to logfile, buffer or stdout
     *
     * @param mixed|string    $msg   :   string or array to logging
     * @param bool $addBR
     */
	public function msg($msg, $addBR=TRUE)
	{
		if (isset($msg) && is_array($msg))
		{
			foreach($msg as $line)
			{
				$this->msg($line, $addBR);
			}
			return;
		}
		if ($addBR) $msg .= "\n";
		if ($this->mode)
		{
			$this->buffer .= $msg;
		}
		else if ($this->eachWrite)
		{
			if ($this->open(TRUE))
			{
                switch($this->outputEncoding)
				{
                    case self::OUT_ENCODING_UTF8_ENC : fputs($this->log, utf8_encode($msg)); break;
                    case self::OUT_ENCODING_UTF8_DEC : fputs($this->log, mb_convert_encoding( $msg, 'CP1252', 'UTF-8')); break;
                    default : fputs($this->log, $msg);
				}
				$this->close();
				$this->openTyp[0] = "a";
			}
		}
		else if ($this->log)
		{
            switch($this->outputEncoding)
            {
                case self::OUT_ENCODING_UTF8_ENC : fputs($this->log, utf8_encode($msg)); break;
                case self::OUT_ENCODING_UTF8_DEC : fputs($this->log, mb_convert_encoding( $msg, 'CP1252', 'UTF-8')); break;
                default : fputs($this->log, $msg);
            }
		}
	}
	/** 
	*	Put the string "23.12.2004 14:22.55" + $msg + linefeed
	*	to logfile, buffer or stdout
	*   
	*	@param	string	$msg    String to logging
	*	@param	string	$format     :   Date,Time format => default "23.12.2004 14:22.55"
	*/
	public function timeMsg($msg,$format="d.m.Y H:i.s")
	{
		$this->msg(sprintf("%s %s", date($format), $msg));
	}

    /**
     *    Put the string  INFO_PREFIX + $msg + linefeed
     *    to logfile, buffer or stdout
     *
     * @param string    $msg            :   String to info logging
     * @param bool      $toStdOutput
     * @return bool
     */
	public function infoMsg($msg, $toStdOutput=FALSE)
	{
		$ret = true;
		if ($this->infoOutputFilter)
		{
			$this->msg($this->INFO_PREFIX.$msg);
			if ($this->infoToStdout || $toStdOutput) 
			{
				if (is_null($this->stdOutFunction))
				{
					printf("%s%s\n", $this->INFO_PREFIX, $msg);
				}
				else
				{
					print(call_user_func($this->stdOutFunction, sprintf("%s%s\n", $this->INFO_PREFIX, $msg)));
				}
			}
			if (!is_null($this->infoMsgHandler)) $ret = call_user_func($this->infoMsgHandler, $msg, INFO);
		}
		return $ret;
	}

    /**
     *    Put the string  WARN_PREFIX + $msg + linefeed
     *    to logfile, buffer or stdout
     *
     * @param string    $msg        :   String to warn logging
     * @return bool
     */
	public function warnMsg($msg)
	{
		$ret = true;
		if ($this->warningOutputFilter)
		{
			$this->msg($this->WARNING_PREFIX.$msg);
			if ($this->warningToStdout) 
			{
				if (is_null($this->stdOutFunction))
				{
					printf("%s%s\n", $this->WARNING_PREFIX, $msg);
				}
				else
				{
					print(call_user_func($this->stdOutFunction, sprintf("%s%s\n", $this->WARNING_PREFIX, $msg)));
				}
			}
			if (!is_null($this->warningMsgHandler)) $ret = call_user_func($this->warningMsgHandler, $msg, WARNING);
		}
		return $ret;
	}

    /**
     *    Put the string  ERROR_PREFIX + $msg + linefeed
     *    to logfile, buffer or stdout
     *
     * @param string    $msg        :   String to error logging
     * @return bool|mixed
     */
	public function errorMsg($msg)
	{
		$ret = true;
		$this->msg($this->ERROR_PREFIX.$msg);
		$this->errorCounter += 1;
		if ($this->errorToStdout) 
		{
			if (is_null($this->stdOutFunction))
			{
				printf("%s%s\n", $this->ERROR_PREFIX, $msg);
			}
			else
			{
				print(call_user_func($this->stdOutFunction, sprintf("%s%s\n", $this->ERROR_PREFIX, $msg)));
			}
		}
		if (!is_null($this->errorMsgHandler)) $ret = call_user_func($this->errorMsgHandler, $msg, ERROR);
		return $ret;
	}
	/**
	*	Put each data type to logfile, buffer or stdout
	*   
	*	@param	mixed	$val        :   all data types
	*	@param	string	$notes      :   notes in the title for this dump
	*/
	public function dump($val, $notes="")
	{
		ob_start();
		var_dump($val);
		$str  = "------- start dump $notes ------------------------------------------\n";
		$str .= ob_get_contents();
		$str .= "------- end of dump $notes -----------------------------------------";
		ob_end_clean();
		$this->msg($str);
	}

    /**
     *    This is the standard printf function to logfile, buffer or stdout
     *
     * @internal param string $format Standard printf format string see PHP docs
     * @internal param $mixed ......      each paramter defined in format
     */
	public function printf( )
	{
        /** @var int $numargs  */
	    $numargs = func_num_args();
        /** @var array $arg_list */
		$arg_list = func_get_args();
		$realStr = "";
		$str = "";
   		for ($i = 0; $i < $numargs; $i++)
		{
		    if ($i > 0)
			{
				$str .= ",";
			}
			else
			{
			    $str = "\$realStr = sprintf(";
			}
		    if (is_string($arg_list[$i]) )
		    {
		        $str .= "\"";
				$str .= str_replace('"', '\\"', $arg_list[$i]);
				$str .= "\"";
		    }
		    else
		    {
				$str .= $arg_list[$i];
			}
			
		}
		$str .= ");";
		eval($str);
		$this->msg($realStr);
    }
	/**
	*	This function read from logfile or buffer
	*	This function dose not work with stdout
	*    
	*	@param	int		$pos	Offset in file or buffer
	*	@param	int		$len    Size of byte
	*	@return	string			bytes from file
	*/
    public function read($pos, $len)
    {
    	$str = FALSE;
    	if ($this->useStdout) return $str;
		if ($this->mode)
		{
			$len = strlen($this->buffer);
			if ($pos < $len)
			{
				$str = substr($this->buffer, $pos, $len);
			}
		}
		else if ($this->readWrite)
		{
			if ($this->eachWrite)
			{
				$this->openTyp = "r";
				if ($this->open(TRUE))
				{
					fseek($this->log, $pos);
					$str = fread($this->log, $len);
					$this->close();
				}
				$this->openTyp = "a";
				return $str;
			}
			else if ($this->log)
			{
				fseek($this->log, $pos);
				$str = fread($this->log, $len);
			}
		}
		return $str;
    }
	/** 
	*	This function read the first line (incl. \n) from logfile or buffer
	*	This function dose not work with stdout
	*	
	*	@param	int		$seekPos	Set the postion for first line
	*	@return	string	
	*/
    public function firstLine($seekPos=0)
    {
    	$this->seekPos = $seekPos;
        return $this->nextLine();
    }
	/** 
	*	This function read the next line (incl. \n) from logfile or buffer
	*	This function dose not work with stdout
	*	
	*	@return	string
	*/
    public function nextLine()
    {
    	$str = FALSE;
    	if ($this->useStdout) return $str;
		if ($this->mode)
		{
			$len = strlen($this->buffer);
			if ($this->seekPos < $len)
			{
				$t = strpos($this->buffer, "\n", $this->seekPos);
				$str = substr($this->buffer, $this->seekPos, $t -$this->seekPos +1);
				$this->seekPos = $t +1;
			}
		}
		else if ($this->readWrite)
		{
			if ($this->eachWrite)
			{
				$this->openTyp = "r";
				if ($this->open(TRUE))
				{
					fseek($this->log, $this->seekPos);
					$str = fgets($this->log);
					$this->seekPos = ftell($this->log);
					$this->close();
				}
				$this->openTyp = "a";
				return $str;
			}
			else if ($this->log)
			{
				fseek($this->log, $this->seekPos);
				$str = fgets($this->log);
				$this->seekPos = ftell($this->log);
				// special between "a+" and "w+"
				if ($this->openTyp[0] == "w") fseek($this->log, 0, SEEK_END);
			}
		}
		return $str;
    }
	/** 
	*	This function write the buffer to the file
	*	This function do not work with stdout
	*/
    public function flush()
    {
		if (!$this->mode)
		{
			if ($this->log)
			{
				fflush($this->log);
			}
		}
    }
 	/** 
	*	This function get the message prefix for errorMsg
	*	    
	*   @return string
	*/
	public function getERROR_PREFIX()
	{
		return $this->ERROR_PREFIX;
	}
 	/** 
	*	This function get the message prefix for warnMsg
	*	    
	*   @return string
	*/
	public function getWARNING_PREFIX()
	{
		return $this->WARNING_PREFIX;
	}
 	/** 
	*	This function get the message prefix for InfoMessage
	*	    
	*   @return string
	*/
	public function getINFO_PREFIX()
	{
		return $this->INFO_PREFIX;
	}
 	/** 
	*	This function set the message prefix for errorMsg
	*   
	*	@param	string	$prefix
	*/
	public function setERROR_PREFIX($prefix)
	{
		$this->ERROR_PREFIX = $prefix;
	}
 	/** 
	*	This function set the message prefix for warnMsg
	*   
	*	@param	string	$prefix
	*/	public function setWARNING_PREFIX($prefix)
	{
		$this->WARNING_PREFIX = $prefix;
	}
 	/** 
	*	This function set the message prefix for InfoMessage
	*   
	*	@param	string	$prefix
	*/	public function setINFO_PREFIX($prefix)
	{
		$this->INFO_PREFIX = $prefix;
	}
	/** 
	*	This function get the value of the internal error counter
	*    
	*   @return		int
	*/	
	public function getErrorCounter()
	{
		return $this->errorCounter;
	}
	/**
	*	This function set the value of the internal error counter
	*    
	*   @param	int		$cnt
	*/	
	public function setErrorCounter($cnt=0)
	{
		$this->errorCounter = $cnt;
	}
	/**
	*	Read the act file position, only in readwrite or buffer mode
	*	
	*	@return		int  => error boolean FALSE
	*/
    function getPos()
    {
		if ($this->mode)
		{
			return strlen($this->buffer);
		}
		else if ($this->readWrite)
		{
			if ($this->eachWrite)
			{
				$pos = 0;
				$this->openTyp = "r";
				if ($this->open(TRUE))
				{
					fseek($this->log, 0, SEEK_END);
					$pos = ftell($this->log);
					$this->close();
				}
				$this->openTyp = "a";
				return $pos;
			}
			else if ($this->log)
			{
				return ftell($this->log);
			}
		}
		return FALSE;
    }
    /** 
	*	This function set the filter for infoMsg
	*   
	*	@param	boolean 	$output
	*/	
	public function setFilterInfo($output=TRUE)
	{
		$this->infoOutputFilter = $output;
	}
	/** 
	*	This function get the filter for infoMsg
	*   
	*   @return	boolean  	=> output filter
	*/	
	public function getFilterInfo()
	{
		return $this->infoOutputFilter;
	}
    /** 
	*	This function set the filter for warningMsg
	*   
	*	@param	boolean 	$output
	*/	
	public function setFilterWarning($output=TRUE)
	{
		$this->warningOutputFilter = $output;
	}
	/** 
	*	This function get the filter for warningMsg
	*   
	*   @return	boolean  	=> output filter
	*/	
	public function getFilterWarning()
	{
		return $this->warningOutputFilter;
	}
	/**
	*	set std output
	*
	* @return void
	* @param  $err		: [true/false]  setting for std output
	* @param  $warn		: [true/false]  setting for std output
	* @param  $info		: [true/false]  setting for std output
	*/
	public function setStdOutput($err=FALSE, $warn=FALSE, $info=FALSE)
	{
		$this->errorToStdout = $err;
		$this->warningToStdout = $warn;
		$this->infoToStdout = $info;
	}
	/**
	*	set std output
	*
	* @return void
	* @param  $err		: [true/false]  setting for std output
	* @param  $warn		: [true/false]  setting for std output
	* @param  $info		: [true/false]  setting for std output
	*/
	static function __setStdOutput($err=FALSE, $warn=FALSE, $info=FALSE)
	{
		Global $ErrorLogger;
		if ( isset($ErrorLogger) && is_object($ErrorLogger) )
		{
			$ErrorLogger->errorToStdout = $err;
			$ErrorLogger->warningToStdout = $warn;
			$ErrorLogger->infoToStdout = $info;
		}
	}
	/**
	 * Get activ filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		if ($this->useStdout)
		{
			return "STDOUT";
		}
		return $this->fileName;
	}
	/**
	 * Setzen einer callback function für einen Message Aufruf ERROR|WARNING|INFO
	 *
	 * @param  function,methode $handler	// function messageHandler($msg, $type = [ERROR|WARNING|INFO]) return [true|false]  false = Abbruch der Applikation
	 * @param bool $error
	 * @param bool $warning
	 * @param bool $info
	 */
	public function setMsgHandler($handler, $error=false, $warning=false, $info=false)
	{
		if ($error)
		{
			$this->errorMsgHandler = $handler;
		}
		if ($warning)
		{
			$this->warningMsgHandler = $handler;
		}
		if ($info)
		{
			$this->infoMsgHandler = $handler;
		}
	}
	/**
	 * Auslesen der Einstellung für die output encoding
	 *
	 */
	public function getEncoding()
	{
		return $this->outputEncoding;
	}

    /**
     * Setzen der Einstellung für das output encoding
     *
     * @param int $encoding     : OUT_ENCODING_STD, OUT_ENCODING_UTF8_ENC, OUT_ENCODING_UTF8_DEC
     * @return int              : Aktuelle Einstellung vor dem Ausruf
     */
	public function setEncoding($encoding)
	{
        $ret = $this->outputEncoding;
		$this->outputEncoding = $encoding;
        return $ret;
	}
	public function setStdOutputFunction($name)
	{
		$this->stdOutFunction = $name;
	}
}

?>
