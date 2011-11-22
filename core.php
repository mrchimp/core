<?php if (!defined('IN_SCRIPT')) { header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found"); exit(0); }

/**
 * Database connection and helper function class
 *
 * PHP version 5
 *
 * LICENSE: Unlicensed
 *
 * @author	Jake Gully <jake@deviouschimp.co.uk>
 * @copyright	2011 Jake Gully
 * @license	Unlicensed
 *
 */
  
class Core {

  public $dbh;

  private static $_instance;
  private static $_dsn;
  private static $_user;
  private static $_pass;

  /**
   * Constructor. This is a singleton so do not use. Call getInstance instead.
   */
  private function __construct ($host = null) {
    if ($host == null) {
      define('HOST', $_SERVER['SERVER_NAME']);
    } else {
      define('HOST', $host);
    }
		
    if (@!include 'db_con/'.HOST.'.php') {
      die('Database server is not configured on this server.');
    }
    
    self::$_dsn  = DSN;
    self::$_user = DBUSER;
    self::$_pass = DBPASSWORD;
		
    try {
      $this->dbh = new PDO(self::$_dsn,self::$_user,self::$_pass);
      $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      $this->logEvent("Unable to establish a database connection: " . $e->getMessage(), 5);
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      exit(0);
    }
  }

  /**
   * Returns an instance of the class.
   *
   * Use this instead of __construct. e.g:
   * $core = Core::getInstance()
   *
   * @return object
   */
  public static function getInstance() {
  if(!isset(self::$_instance)){
    $object= __CLASS__;
    self::$_instance=new $object;
  }
  return self::$_instance;
  }
  
  /**
  * Process sql statments using PDO
  *
  * Author: Daniel Hewes
  * Date: October 2011
  *
  * @param string $sql	the SQL to be processed
  * @param array $params the parameters to bind (optional)
  */
  public function executeSQL($sql, $params = array()) {
    try {
      $stmt = $this->dbh->prepare($sql);
      if (empty($params)) { $stmt->execute(); } 
      else { $stmt->execute($params); }
      //$stmt->debugDumpParams();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
      $this->logEvent("An error has occured in the executeSQL Function. 
                        Error acquiring data: " . $e->getMessage(), 5);
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      exit(0);
    }
  }

  /**
   * Neatly echo a variable's type and value.
   *
   * @param string $var	 the value to be written
   * @param string $name the name of the variable being written (optional)
   */
  public function write($var, $name='') {
    echo '<pre>';
    if (!empty($name)) { echo '$', $name, ': '; }
    var_dump($var);
    echo '</pre>';
  }

  /**
   * Echo an array's contents within nested divs
   *
   * @param array $array   the array to be written
   * @param int   $depth   do not set manually. used when the function calls itself
   * @param int   $recurse do not set manually. used when the function calls itself
   */
  public function writeArrayNicely($array, $depth=1, $recurse=true) {
    if (!is_array($array)) {
      return '<p>That wasn\'t an array.</p>';
    }
    $outstr = '';
    $outstr .= '<div style="border:1px solid #333333;padding:5px;margin:20px;background-color:';
    $outstr .= $this->depthHex($depth) . ';">';
    $outstr .= 'array(' . sizeof($array) . ') {<br>';
    foreach($array as $key=>$value) {
      $outstr .= '<div style="border:1px solid #333333;padding:5px;margin:5px;background-color:';
      $outstr .= $this->depthHex($depth+2) . ';">';
      for ($x=0;$x<$depth;$x++) {
        $outstr .= '&nbsp;&nbsp;&nbsp;&nbsp;';
      }
      $outstr .= '[' . (gettype($key) == 'string' ? '"' : '') . $key;
      $outstr .= (gettype($key) == 'string' ? '"' : '' ) . '] => ';
      if (gettype($value) == 'array') {
        if ($key == 'GLOBALS') {
          $outstr .=  '$_GLOBALS [not showing to avoid infinite recursion]';
        } else {
          if ($recurse==true){
            $outstr .= $this->writeArrayNicely($value, ($depth+3));
          }
        }
      } elseif (gettype($value) == 'object') {
        $outstr .=  'Object';
      } else {
        $outstr .=  gettype($value) . ' (' . sizeof($value) . ') "' . strval($value) . '"';
      }
      $outstr .=  '<br></div>';
    }
    
    for ($x=1;$x<$depth;$x++) {
      $outstr .=  '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    
    $outstr .=  '}</div>';
    return $outstr;
  }

  /**
   * Convert array depth to a hex color
   *
   * Used by WriteArrayNicely().
   *
   * @param int $depth	the level of nesting. The higher the number the darker the resulting colour.
   */
  private function depthHex($depth) {
    $color_val = (16 - ($depth * 1));
    if ($color_val < 0) { $color_val = 0; }
    $color_val = dechex($color_val);
    $color = "#$color_val$color_val$color_val";
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
   * Returns the number of milleseconds elapsed since startTimer() was called
   * 
   * @param string $what the action which was timed, e.g. a function name, page name, db query.
   * Example usage: getTime($_SERVER['PHP_SELF']);
   */
  public function getTime ($what = null) {
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $this->starttime);
    $this->logEvent("$what took $totaltime seconds to load.", 1);
  }
  
  /**
   * Log a string to file
   *
   * Author: Daniel Hewes
   * Date: October 2011
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
   * Author: Daniel Hewes
   * Date: October 2011
   *
   * @param string $subject	the subject of the email
   * @param string $mail_body the body text of the email
   *
   * Assumes all inputs have been validated
   * This needs to be secured!! 'TO:', 'CC:', 'CCO:' or 'Content-Type' should be stripped from $mail_body.
   * ***DO NOT*** set logEvent $type=5. It might cause the mailSend function to call itself infinitely.
   * There is no decent error handling for this function.  Try/catch will not work. The error procuded is output and not classed as an exception.
   * Look in to set_error_handler. 
   */
  public function mailSend($subject, $mail_body, $from = NULL) {
    $recipient = EMAIL;
    if (empty($from)) { $header = 'From: ' . EMAIL; } 
    else { $header = 'From: ' . $from; }
    if(@mail($recipient, $subject, $mail_body, $header)) {
      return true;
    } else {
      $this->logEvent("mailSend function failed. Subject: $subject | Message was: $mail_body", 4);
      return false;
    }
  }
}
