<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up All OCI commands are
  commands to the Oracle libraries. To get the file to work, you must place it
  somewhere where your Apache server can run it, and you must rename it to have
  a ".php" extension. You must also change the username and password on the
  oci_connect below to be your ORACLE username and password
-->

<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
/* content. Delete or comment out this block when it's no longer needed. */ 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_apiemont";
$config["dbpassword"] = "a90501727";
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()
$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<html>

<head>
    <title>Database Projection Interface</title>
    <style>
       body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    text-align: center;
}

h2 {
    color: #333;
}

form {
    margin-bottom: 20px;
}

select {
    padding: 10px;
    font-size: 16px;
}

#tableFormContainer {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.tableForm {
    background-color: #fff;
    border: 1px solid #ddd;
    padding: 20px;
    margin: 10px;
    display: none;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.styledButton {
    background-color: #4CAF50;
    color: white;
    padding: 15px 30px;
    margin: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.styledButton:hover {
    background-color: #45a049;
}

input[type="text"] {
    padding: 10px;
    margin: 5px;
    width: 100%;
    box-sizing: border-box;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 15px;
    border: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #4CAF50;
    color: white;
}

.dropdown-container {
            width: 200px; /* Adjust the width as needed */
            margin: 20px; /* Adjust margin as needed */
        }

        /* Style for the dropdown itself */
        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* Optional: Style for dropdown options */
        select option {
            background-color: #f4f4f4;
            color: #333;
        }
    </style>
</head>

<body>
	<div class="center-container">
	<h2>Home Page</h2>
	<p>Set back to default. If this is the first time you're running this page, you MUST use reset </p>

	<form method="POST" action="homepage.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
		<p><input type="submit" value="Reset" name="reset" class="styledButton"></p>
	</form>
	
	<p> Access Data and Edit Data in All Tables </p>
	<form method="POST" action="projection.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" value="DataControl" class="styledButton"></p>
	</form>
	<form method="POST" action="company.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" value="Selection"  class="styledButton"></p>
	</form>
	
	<p> Query the Database </p>
	<form method="POST" action="advancedqueries.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" value="SearchTables" class="styledButton"></p>
	</form>


	<?php
	// The following code will be parsed as PHP

	function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/javascript'>alert('" . $message . "');</script>";
		}
	}

	function executePlainSQL($cmdstr)
	{ //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;

		$statement = oci_parse($db_conn, $cmdstr);
		//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = oci_error($statement); // For oci_execute errors pass the statementhandle
			echo htmlentities($e['message']);
			$success = False;
		}

		return $statement;
	}


	function connectToDB()
	{
		global $db_conn;
		global $config;

		// Your username is ora_(CWL_ID) and the password is a(student number). For example,
		// ora_platypus is the username and a12345678 is the password.
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
		$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

		if ($db_conn) {
			debugAlertMessage("Database is Connected");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
	}

	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

	// Function to run all SQL statment in FILE
	function handleResetRequest()
	{
		global $db_conn;
		// Read the content of the SQL script
		$sqlScript = file_get_contents('initialization.sql');
	
		// Break the script into individual SQL statements
		$sqlStatements = explode(';', $sqlScript);
	
		// Execute each SQL statement
		foreach ($sqlStatements as $sqlStatement) {
			if (trim($sqlStatement) != '') {
				executePlainSQL($sqlStatement);
			}
		}
		oci_commit($db_conn);
	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
			} 
			disconnectFromDB();
		}
	}

	if (isset($_POST['reset'])) {
		handlePOSTRequest();
	} 
	// End PHP parsing and send the rest of the HTML content
	?>
	</div>
</body>

</html>


