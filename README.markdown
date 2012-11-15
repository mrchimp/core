# core.php #

Manages PDO connection and provides basic debugging functions.


## Installation ##

 *  Put core/ into the root of your website.
 *  Create a config file for your server by duplicating or renaming **core/db_con/localhost-example.php** to **core/db\_con/HOSTNAME.php**. E.g. localhost.php or example.com.php
 *  Update this file with your database connection details.
 *  Point your browser towards http://HOSTNAME/core/example.php
 *  Scroll down and you will either see "everything worked!" or some errors.
 *  If you have errors it's not my fault.


## Usage ##

Setting up core in your script:

    // Prevent direct access.
    define('IN_SCRIPT', true);

    // Include the class definition
    require_once 'core.php';

    // Instantiate
    $core = Core::getInstance();

For further examples see core/example.php





## Public Methods ##


### getInstance() ###


Returns an instance of Core.



### executeSQL(string $sql, array $params = array()) ###

Executes SQL.



### write($var, string $name='') ###

For debugging purposes.

Dumps the contents of a variable between PRE tags. If $name is specified it will be written out first. Sometimes handy if you're using write() a lot.



### writeArrayNicely(array $array, bool $recurse=true) ###

For debugging purposes.

Takes an array and writes it in nested divs with greyscale backgrounds representing the depth within the array. Works with n-dimension arrays. 

Set $recurse to false to prevent it writing out the contents of arrays within the array.

Note: A hack is included to allow $GLOBALS to be passed to this function - anything with the key 'GLOBALS' will not be written out.



### startTimer() ###

Starts the timer.



### getTime(string $what = null) ###

Returns the number of seconds elapsed since startTimer() was called. Returned value is a float to millisecond accuracy.



### logEventTime($event\_name) ###

Logs the time taken since startTimer() was called to the log file defined in the config file.



### logEvent(string $message, int $type) ###

Logs an email to the file specified in db\_con/HOSTNAME.php. If $type is 5 then emails the error to the email address specified in the above file.

    $type:
    1 = Information
    2 = Audit
    3 = Security
    4 = Debug
    5 = Error



### mailSend(string $subject, string $mailBody, string $from) ###

Sends an email to the email address specified in db\_con/HOSTNAME.php.
