<!DOCTYPE HTML>
<html lang="en">
<head>
  <title>Core.php Test Page</title>
  <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
  <style>
    body {
      font-family: arial, sans-serif;
      font-size: 14px;
    }
  </style>
</head>
<body>

<?php
  
  /**
   * Setting up
   */ 
  
  // Define IN_SCRIPT
  define('IN_SCRIPT', 'what the hell');

  // include the class definition
  require_once 'core.php';

  // Use getInstance rather than "new Core()"
  $core = Core::getInstance();

  
  $core->startTimer();
?> 
  

  
  
<h2>Using write($test)</h2>

<?php
  $test = array(
    1, 
    2, 
    'foo', 
    'bar', 
    array(5, 6, 7), 
    $core,
    'this' => 'that',
    'true' => false
  );

  echo $core->write($test, 'test');
?>




<h2>Using writeArrayNicely($GLOBALS)</h2>

<?php
  echo $core->writeArrayNicely($GLOBALS);
  
  // Note: The globals variable contains a reference to itself.
  //       writeArrayNicely() contains a workaround to prevent
  //       infinite recursion. However this only works for the
  //       $GLOBALS array - other recursive arrays may break it.
?>

  
  
  
<h2>Creating test table using executeSQL()...</h2>

<?php
  if ($core->dbh->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
    $sql = 'CREATE TABLE IF NOT EXISTS test_table 
                    (
                      id INTEGER PRIMARY KEY AUTOINCREMENT, 
                      name TEXT
                    )';
  } else {
    $sql = 'CREATE TABLE IF NOT EXISTS test_table 
                    (
                      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                      name TEXT
                    )';
  }
  
  $result = $core->executeSQL($sql);
  
  if ($result) {
    echo '<p>Success!</p>';
  } else {
    echo '<p>Problem creating test table. Exiting...</p>';
    exit();
  }
?>



  
<h2>Inserting some user data using executeURL()...</h2>

<?php
  $sql = "INSERT 
                 INTO test_table 
                   (name)
                 VALUES
                   ('Alice')";

  $result = $core->executeSQL($sql);
  
  if ($result) {
    echo '<p>Success!</p>';
  } else {
    echo '<p>Error inserting names. Exiting...</p>';
    exit();
  }
?>
  

  
  
<h2>Selecting some data using executeSQL()...</h2>

<?php
  $sql = 'SELECT id, name FROM test_table';

  $data = $core->executeSQL($sql);

  if (is_array($data)) {
    echo '<p>Success!</p>';
    
    echo '<h2>Writing data with write()...</h2>';
    $core->write($data);

    echo '<h2>Writing data with writeArrayNicely()...</h2>';
    $core->writeArrayNicely($data);
    
  } else {
    echo('No results found.');
    exit();
  }
?>
  

  
  
<h2>Deleting data...</h2>

<?php
  $sql = 'DELETE FROM test_table';

  if($core->executeSQL($sql)){
    echo '<p>Success!</p>';
  } else {
    echo '<p>Failed. Exiting.</p>';
    exit();
  }
?>

  
  
  
<h2>Dropping test table... </h2>

<?php
  $sql = 'DROP TABLE test_table';
  
  if ($core->executeSQL($sql)) {
    echo '<p>Success!</p>';
  } else {
    echo '<p>Failed. Exiting.</p>';
    exit();
  }

  
  
  
  /**
   * Wrap it up.
   */
  echo '<br>That all took '.$core->getTime().' seconds. <br><br>';
  echo 'If you\'re reading this then everything worked!';

?>

</body>
</html>