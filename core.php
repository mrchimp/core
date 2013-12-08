<?php 

defined('IN_SCRIPT') or die('No direct access.');

/**
 * Database connection and helper function class
 *
 * PHP version 5
 *
 * LICENSE: Unlicensed
 *
 * @author    Jake Gully <jake@deviouschimp.co.uk>
 * @author    Daniel Hewes <daniel@danimalweb.co.uk>
 * @copyright 2011 Jake Gully and Daniel Hewes
 * @link      http://github.com/mrchimp/core
 * @license   Unlicensed
 */

 
class Core {

  public $dbh;

  private static $_instance;
  private $settings;

  /**
   * Constructor!
   *
   * @param Array $settings
   */
  function __construct (array $user_settings = array()) {

    set_error_handler(array($this, 'coreErrorHandler'));
    
    $this->settings = array(
      'debug'      => false,
      'username'   => '',
      'password'   => '',
      'email'      => '',
      'dsn'        => 'sqlite:' . __DIR__ . '/db/database.db',
      'config_dir' => __DIR__ . '/config/',
      'log_file'   => __DIR__ . '/custom.log',
    );

    $config_file = $this->settings['config_dir'].$_SERVER['SERVER_NAME'].'.php';

    if (!is_array($user_settings)) {
      if (file_exists($config_file)) {
        if (!$user_settings = @include($config_file)) {
          $user_settings = array();
        }
      } else {
        $user_settings = array();
      }
    }
   
    $this->settings = array_merge($this->settings, $user_settings);
		
    if ($this->settings['debug']) {
      $this->setDebug(true);
    }

    try {
      $this->dbh = new PDO(
        $this->settings['dsn'],
        $this->settings['username'],
        $this->settings['password']
      );
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      var_dump($this->settings);
      trigger_error('Unable to establish a database connection: '.$e->getMessage(), E_USER_ERROR);
      exit(0);
    }
  }

  /**
   * Returns an instance of the class.
   *
   * You can use this instead of __construct. e.g:
   * $core = Core::getInstance()
   *
   * @return object
   */
  public static function getInstance() {
    if(!isset(self::$_instance)){
      $object= __CLASS__;
      self::$_instance = new $object;
    }
    return self::$_instance;
  }
  
  /**
   * Process sql statements using PDO
   *
   * @author    Daniel Hewes
   * @date      October 2011
   *
   * @param string $sql	the SQL to be processed
   * @param array  $params the parameters to bind (optional)
   */
  public function executeSQL($sql, $data = array()) {
    try {
      $stmt = $this->dbh->prepare($sql); //Used by all query types.

      if (substr($sql, 0, 6) === "SELECT") { 
        // Select
        $stmt->execute($data);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); //Returns an Array if data returned. Use is_array to check.
      } else { 
        // Create/Insert/Update/Delete/Drop  
        if ($this->dbh->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite'
        && isset($data[0]) 
        && gettype($data[0]) == 'array') {
          foreach ($data as $datum) {
            $stmt->execute($datum);
          }
          return true;
        } else {
          return $stmt->execute($data); //Returns true/false
        }
      }
      
    } catch(PDOException $e) {
      trigger_error(
        'Core::executeSQL() encountered a problem: ' . $e->getMessage() .
        (isset($stmt) ? ' Debug Params: ' . $stmt->debugDumpParams() : ''), 
        E_USER_ERROR);
      exit(0);
    }
  }

  /**
   * Neatly echo a variable's type and value.
   *
   * @param string $var	 the value to be written
   * @param string $name the name of the variable being written (optional)
   */
  public function write($var, $name = '') {
    $o = '<pre>START DUMP';
    if (!empty($name)) {
      $o .= " $name";
    }
    $o .= ':<br>';
    $raw_var = var_export($var, true);
    $raw_var = htmlspecialchars($raw_var);
    $o .= $raw_var;
    $o .= '<br>';
    $o .= 'END DUMP</pre>';
    return $o;
  }

  /**
   * Return an array's contents within nested divs
   *
   * @param array $array   the array to be written
   * @param bool  $recurse set to false to only write the first 
   *                       layer of the array.
   *                       Prevents the function from recursively 
   *                       calling itself.
   * @param int   $depth   do not set manually. used when the 
   *                       function calls itself
   * @return string        the generated string
   */
  public function writeArrayNicely($array, $recurse=true, $depth=1) {
    $o = '';

    if ($depth == 1) {
      $o .= '<style>
            .nicearray {
              border:  1px solid #333333;
              padding: 5px;
              margin:  10px 10px 10px 20px; 
            }
            </style>';
    }

    if (!is_array($array)) return '<p>That wasn\'t an array.</p>';
    
    if ($depth == 1) {
      $o .= '<div class="nicearray" style="background-color:'.$this->depthHex($depth).';">';
    }

    // write number of elements in array
    $o .= 'array('.sizeof($array).')<br>';

    // For each item in array
    foreach($array as $key=>$value) {
      $o .= '<div class="nicearray" style="background-color:'.$this->depthHex($depth+2).';">';
      
      // Write value's variable type
      if (gettype($key) == 'string') {
        $o .= '[\''.$key.'\'] =&gt; ';
      } else {
        $o .= '['.$key.'] =&gt; ';
      }
      
      // Write value
      if (is_array($value)) {
        if ($key === 'GLOBALS') {
          $o .=  '$_GLOBALS [not showing to avoid infinite recursion]';
        } else {
          if ($recurse==true) {
            $o .= $this->writeArrayNicely($value, true, ($depth+1));
          }
        }
      } else if (is_object($value)) {
        $o .= 'object - instance of '.get_class($value);
      } else if (is_bool($value)) {
        $o .= 'boolean(1) '.($value ? 'True' : 'False');
      } else if (is_string($value)) {
        $o .= 'string('.strlen($value).') \''.strval($value).'\'';
      } else if (is_int($value)) {
        $o .= 'integer: '.strval($value);
      } else {
        $o .= gettype($value).': "'.strval($value).'"';
      }

      $o .= '</div>';
    }

    if ($depth == 1) {
      $o .= '</div>';
    }
    return $o;
  }

  /**
   * Convert array depth to a hex color
   *
   * Used by WriteArrayNicely().
   *
   * @param int $depth the level of nesting. 
   *                   The higher the number the darker the resulting colour.
   * @return string the generated hex color, including leading #
   */
  private function depthHex($depth) {
    $val = (16 - ($depth * 1));
    if ($val < 0) {
      $val = 0;
    }
    $val = dechex($val);
    $color = "#$val$val$val";
    return $color;
  }

  /**
   * Starts a millisecond-accurate timer
   */
  public function startTimer() {
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $this->starttime = $mtime;
  }

  /**
   * Returns the number of seconds elapsed since startTimer() was called
   * Example usage: getTime($_SERVER['PHP_SELF']);
   * 
   * @param string $what the action which was timed 
   *                     e.g. a function name, page name, db query.
   */
  public function getTime() {
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $this->starttime);
    return $totaltime;
  }
 
  /**
   * Logs the duration of an event in seconds
   *
   * @param string $event_name the name of the event being timed
   */
  public function logEventTime($event_name) {
    $time = $this->getTime();
    $this->logEvent("$event_name took $time seconds.", 1);
  }

  /**
   * Log a string to file
   *
   * @param string $msg	the string to be logged
   * @param int $type the type of message 
   *		1:Information
   *		2:Audit
   *		3:Security
   *		4:Debug
   *		5:Fatal - Sends email
   */
  public function logEvent($msg, $type) { 
    $str = '['.date("D M d G:i:s Y").'] ';

    switch ($type) {
      case 1:
        $str .= ('[info] ');
        break;
      case 2:
        $str .= ('[audit] ');
        break;
      case 3:
        $str .= ('[security] ');
        break;
      case 4:
        $str .= ('[debug] ');
        break;
      case 5:
        $str .= ('[error] ');
        break;
    }

    $str .= '[client '.$_SERVER['REMOTE_ADDR'].'] ';
    $str .= $msg . "\n";

    error_log($str, 3, LOG_FILE);

    if($type == 5) {
      $this->mailSend("Fatal Error: " . $_SERVER['HTTP_HOST'], $str);
    }
  }
  
  /**
   * Sends an email
   *
   * Used by logToFile().
   *
   * @param string $subject	the subject of the email
   * @param string $mail_body the body text of the email
   *
   * Assumes all inputs have been validated
   * This needs to be secured!! 'TO:', 'CC:', 'CCO:' or 'Content-Type' should 
   * be stripped from $mail_body. 
   * ***DO NOT*** set logEvent $type=5. It might cause the mailSend function 
   * to call itself infinitely. There is no decent error handling for this 
   * function.  Try/catch will not work. The error produced is output and
   * not classed as an exception. Look in to set_error_handler. 
   */
  public function mailSend($subject, $mail_body, $from = NULL) {
    $recipient = EMAIL;
    $header    = 'From: ' . (empty($from) ? EMAIL : $from);

    if(@mail($recipient, $subject, $mail_body, $header)) {
      return true;
    } else {
      $this->logEvent("mailSend function failed. Subject: $subject | Message was: $mail_body", 4);
      return false;
    }
  }
  
  /**
   * Error handler for all function within core class.
   *
   * @param int $err_code the numeric error code
   * @param str $err_str  description of the error
   * @param str $err_file the name of the file that contains the error
   * @param int $err_line the line number of the error
   */
  public function coreErrorHandler($err_code, $err_str, $err_file, $err_line) {

    $error_type = array (
                  E_WARNING       => 'Warning',
                  E_NOTICE        => 'Notice',
                  E_USER_ERROR    => 'User Error',
                  E_USER_WARNING  => 'User Warning',
                  E_USER_NOTICE   => 'User Notice');
  
    $err = sprintf('PHP %s:  %s in %s on line %d ', $error_type[$err_code], $err_str, $err_file, $err_line);
    $err .= '[client '.$_SERVER['REMOTE_ADDR'].']';
    $err .= "\n";
    
    if (ini_get('log_errors')) {
      error_log($err, 0);
      if($err_code == E_USER_ERROR) {
        if (!$this->isDebugOn()) {
          //$this->mailSend('Fatal Error: '.$_SERVER['HTTP_HOST'].' '.date("Y-m-d H:i:s (T)"), $err);
        }
      }
    }
    
    if ($this->isDebugOn()) {
      print($err);
    } else { //PRODUCTION
      echo 'Computer says no.';
      exit(0);
    }
    
    return true;
  }

  /**
   * turns debug mode on or off
   *
   * @param bool $onoff if true debug mode is turned on, if 
   *                    false debug mode is disabled
   */
  private function setDebug($onoff) {
    if ($onoff) {
      ini_set('display_errors', 1);
    } else {
      ini_set('display_errors', 0);
    }
  }

  /**
   * gets debug mode's status
   *
   * @return bool true if debug mode is enabled
   */
  private function isDebugOn() {
    if (ini_get('display_errors')) {
      return true;
    } else {
      return false;
    }
  }
  
  /**
    * Replaces any parameter placeholders in a query with the value of that	  	
    * parameter. Useful for debugging. Assumes anonymous parameters from 	  	
    * $params are are in the same order as specified in $query
    *	  	
    * By bigwebguy http://stackoverflow.com/q/210564/130347	  	
    * 	
    * @param  string $query  The sql query with parameter placeholders  	
    * @param  array  $params The array of substitution parameters	  	
    * @return string The interpolated query  	
    */
  public static function interpolateQuery($query, $params) {
    $keys = array();

    // build a regular expression for each parameter
    foreach ($params as $key => $value) {
        if (is_string($key)) {
            $keys[] = '/:'.$key.'/';
        } else {
            $keys[] = '/[?]/';
        }
    }
	  	
    $query = preg_replace($keys, $params, $query, 1, $count);
  	
    return $query;
  }

  /**
   * Make sql statements using assoc array
   *
   * @author    Daniel Hewes
   * @date      December 2013
   * 
   * @param  string  $type  Either 'update' or 'insert'
   * @param  array   $data  The data to use
   * @param  string  $table Database table to use
   * @param  integer $id    
   * @return string  The generated SQL query
   */
  public function makeSQL($type, $data, $table, $id = NULL) {

    if ( is_array($data) ) { //We have an array so its all good.

      switch ($type) {
        case 'update':
          return 'this fails';
          $sql = 'UPDATE ' . $table . ' SET ';
          $updates = array();
          
          foreach ($data as $field => $value) {
            
            if( !empty($value) ) { //Only if we have a value.
            
              $value = "'$value'";
              $updates[] = "$field = $value";
            
            }

          }
          
          $sql .= implode(', ', $updates);

          if (!is_null($id)) {
            $sql .= ' WHERE id=' . $data['id'];
          }
          
          break;
            
        case 'insert':
          // Split the fields and values into separate arrays.
          $fields    = array_keys($data);
          $values    = array_values($data);
          $fieldlist = implode(',', $fields);
          $qs        = implode(',', array_fill(0, count($fields), '?'));
          $sql       = "INSERT INTO $table($fieldlist) values($qs)";
          break;
        default:
          trigger_error('Core.php: Invalid type in makeSQL: ' . $type, E_USER_WARNING);
          return false;
      }
  
      return $sql;
    
    } else { //Send an error is not a valid array.
    
        return 'Error. Not a valid array.';
    
    }
  
  }
}
