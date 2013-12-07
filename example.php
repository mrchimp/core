

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
    .success {
      color: #090;
      font-weight: bold;
    }
    .error {
      color: #900;
      font-weight: bold;
    }
  </style>
</head>
<body>

<h2>Loading Core.php file...</h2>
<?php

  // Define IN_SCRIPT
  define('IN_SCRIPT', true);

ini_set('error_reporting', E_ALL);

ini_set('display_errors', 1);

try {
  // include the class definition
  require_once 'core.php';

  // Use getInstance rather than "new Core()"
  $core = new Core();

  $core->startTimer();
} catch (Exception $e) {
  var_dump($e);
  exit();
}
?> 
  
<p class="success">Success!</p>

  
  
  
<h2>Creating test table using executeSQL()...</h2>

<?php

  if ($core->dbh->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
    $sql = 'CREATE TABLE IF NOT EXISTS test_table 
                    (
                      id INTEGER PRIMARY KEY AUTOINCREMENT, 
                      name TEXT,
                      comment TEXT
                    )';
  } else {
    $sql = 'CREATE TABLE IF NOT EXISTS test_table 
                    (
                      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                      name VARCHAR(128),
                      comment VARCHAR(128)
                    )';
  }
  
  $result = $core->executeSQL($sql);
  
  if ($result) {
    echo '<p class="success">Success!</p>';
  } else {
    echo '<p class="error">Problem creating test table. Exiting...</p>';
    exit();
  }

?>



  
<h2>Inserting some user data using executeURL()...</h2>

<?php

  $sql = "INSERT 
         INTO test_table 
           (name,
            comment)
         VALUES
           (:name,
            :comment)";

  $data = array(
    array(
      ':name' => 'Alice',
      ':comment' => 'Hello.'
    ),
    array(
      ':name' => 'Bob',
      ':comment' => 'Goobye.'
    )
  );
                   
  $result = $core->executeSQL($sql, $data);
  
  if ($result) {
    echo '<p class="success">Success!</p>';
  } else {
    echo '<p class="error">Error inserting names. Exiting...</p>';
    exit();
  }

?>
  

  
  
<h2>Selecting some data using executeSQL()...</h2>

<?php
  $sql = 'SELECT id, name FROM test_table';

  $data = $core->executeSQL($sql);

  if (is_array($data)) {
    echo '<p class="success">Success!</p>';
    
    echo '<h2>Writing data with write()...</h2>';
    echo $core->write($data, 'Database data');

    echo '<h2>Writing data with writeArrayNicely()...</h2>';
    echo $core->writeArrayNicely($data);
    
  } else {
    echo('<p class="error">No results found.</p>');
    exit();
  }

?>
  

  
  
<h2>Deleting data...</h2>

<?php
  $sql = 'DELETE FROM test_table';

  if($core->executeSQL($sql)){
    echo '<p class="success">Success!</p>';
  } else {
    echo '<p class="error">Failed. Exiting.</p>';
    exit();
  }
?>

  
  
  
<h2>Dropping test table... </h2>

<?php
  $sql = 'DROP TABLE test_table';
  
  if ($core->executeSQL($sql)) {
    echo '<p class="success">Success!</p>';
  } else {
    echo '<p class="error">Failed. Exiting.</p>';
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