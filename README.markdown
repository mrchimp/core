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


writeArrayNicely(array $array, int $depth=1, bool $recurse=true)
----------------------------------------------------------------

Takes an array and writes it in nested divs with greyscale backgrounds representing the depth within the array. Works with n-dimension arrays. $depth and $recurse should not be set manually. They're for when the function calls itself, which it does, recursively. If an array contains a reference to itself or something weird like that then it will get stuck. There's a hack in to make it work with $\_GLOBALS but this could be fixed to prevent any recursive failure.


startTimer()
------------

Starts the timer.


getTime(string $what = null)
----------------------------

... kind of broken. See issue #1.


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

Sends an email to the email address specified in db\_con/<hostname>.php.

