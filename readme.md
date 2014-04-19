# core.php #

Core is a simple PDO database wrapper and helper. 

 *  Config based on hostname for multiple environments (E.g. Dev/Live)
 *  Catches and log database errors
 *  Time events
 *  Core also provides a handful of useful debugging functions


## Project Status ##

This project was started years ago in my spare time as a quick way to set up database connections for small projects. The Github repo was created so that danimalweb could collaborate on the development. It was never intended as a project for others to use and it's development has reflected that. I'm trying to rectify this and make the repository a stable product that people can use. To this end I am adding version tags. 

I am in the process of converting Core to a non-singleton pattern so that it can be used to connect to multiple databases. I will also be improving the way that the config files are stored and error handling.


## Versions ##

  * v1.0 - The last version to use the singleton pattern.
  * v2.0 - Non-singleton. Improved config handling.


## Installing and Configuring ##

 *  Put the `core` folder somewhere sensible.
 *  Configure core by doing *one* of the following.
   
    **Use a file:** Create a config file for your server by duplicating or renaming `core/config/example.com.php` to `core/config/YOURHOSTNAME.php`. E.g. `localhost.php`. Update this file with your database connection details. Core comes with an empty sqlite database so you can work with zero config.

    **Pass an array:** Pass an array of settings when creating the object. You can also override the file by doing this.

 *  Point your browser towards e.g. `http://localhost/core/example.php`
 *  Scroll down and you will either see "everything worked!" or some errors.
 *  If you have errors it's not my fault.


#### Computer says no ####

This error is designed to not give any information away. If you want more information switch core into debug mode, **just don't leave debug mode on in production!**


## Usage ##

Prevent direct access.

    define('IN_SCRIPT', true);

Include the class definition.

    require_once 'core.php';

Instantiate!

    $core = new Core();

...or...

    $core = new Core($options);

For more information just read `core/example.php`, that pretty much covers everything.


## Settings ##

You can override settings by passing an array when creating a new Core object.

    $core = new Core(array(
        'name': 'value'
    ));


### Available options ###

**name** (Type, Default) - Description

 *  **debug** (Boolean, false) - Set to false to enable display_errors
 *  **username** (String, '') - Database username.
 *  **password** (String, '') - Database password.
 *  **email** (String, '') - Email to mail serious errors to.
 *  **dsn** (String', `'sqlite:'.__DIR__.'/db/database.db'`) - Database connection string.
 *  **config_dir** (String, `__DIR__.'/config'`) - Directory where connection config files are stored.
 *  **log_file** (String, `__DIR__.'/custom.log'`) - File to log errors to.


## Public Methods ##


#### executeSQL (string $sql, array $params = array()) ####

Executes an SQL query.

If the query is a SELECT query then an associative array will be returned. In all other cases a boolean is returned: true if the query was successful, false if not.

$params will be passed to [PDO::execute()](http://php.net/manual/en/pdostatement.execute.php) so should be formatted appropriately.


#### getTime (string $event = null) ####

Returns the number of seconds elapsed since startTimer() was called. Returned value is a float to millisecond accuracy.


#### logEvent (string $message, int $type) ####

Logs an error to the file specified in db\_con/HOSTNAME.php. If $type is 5 then emails the error to the email address specified in the above file.

    $type:
    1 = Information
    2 = Audit
    3 = Security
    4 = Debug
    5 = Error


#### logEventTime ($event\_name) ####

Logs the time taken since startTimer() was called to the log file defined in the config file.


#### mailSend (string $subject, string $mailBody, string $from) ####

Sends an email to the email address specified in db\_con/HOSTNAME.php.


#### makeSQL (string $type, array $data, $id) ####

  > This function is under development. Use with caution. I.e. don't use at all.

Create a simple SQL statement from an array structure.

$type - String. Either `INSERT` or `UPDATE`.

$data - Array. A array of the following structure, or an array of these arrays.

    array(
        'column_name' => $foo,
        'another_col' => $bar
    )

$id -- this needs changing!
  

#### startTimer () ####

Start the timer.


#### write ($var, string $name='') ####

For debugging purposes.

Dumps the contents of a variable between PRE tags. If $name is specified it will be written out first. Sometimes handy if you're using write() a lot.


#### writeArrayNicely (array $array, bool $recurse=true) ####

For debugging purposes.

Takes an array and writes it in nested divs with greyscale backgrounds representing the depth within the array. Works with n-dimension arrays. 

Set $recurse to false to prevent it writing out the contents of arrays within the array.

Note: A hack is included to allow $GLOBALS to be passed to this function - anything with the key 'GLOBALS' will not be written out.


## Depricated Methods ##

#### getInstance () ####

Returns an instance of Core. (The singleton pattern needlessly prevents multiple database connections.)
