core.php
========

Provides PDO connection and some helper functions.


Accessing PDO handler
=====================

    $core = Core::getInstance();
    $core->dbh; // This is the database handler


Public Methods
==============

getInstance()
-------------

Returns an instance of Core.


executeSQL(string $sql, array $params = array())
------------------------------------------------

Executes SQL.


write($var, string $name='')
----------------------------

var\_dump()s $var between PRE tags. If $name is specified it will be written out first. Sometimes handy if you're using write() a lot.


writeArrayNicely(array $array, bool $recurse=true)
----------------------------------------------------------------

Takes an array and writes it in nested divs with greyscale backgrounds representing the depth within the array. Works with n-dimension arrays. Set $recurse to false to prevent it writing out the contents of arrays within the array. This is to prevent an infinite loop if the arraycontains a reference to itself or something weird like that. There's a hack to prevent this happening when writing out $\_GLOBALS but this could be fixed to prevent this happening with any array.


startTimer()
------------

Starts the timer.


getTime(string $what = null)
----------------------------

Returns the number of seconds elapsed since startTimer() was called. Returned value is a float to millisecond accuracy.


logEventTime($event\_name) 
-------------------------

Logs the time taken since startTimer() was called to the log file defined in the config file.


logEvent(string $message, int $type)
------------------------------------

Logs an email to the file specified in db\_con/<hostname>.php. If $type is 5 then emails the error to the email address specified in the above file.

    $type:
    1 = Information
    2 = Audit
    3 = Security
    4 = Debug
    5 = Error


mailSend(string $subject, string $mailBody, string $from)
----------------------------------------------------------

Sends an email to the email address specified in db\_con/HOSTNAME.php.

