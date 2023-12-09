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
$SQL_SANITIZATION_STRING = "'\";()"; 

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
	<form method="POST" action="homepage.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" value="Back" class="styledButton"></p>
	</form>
	<form method="GET" action="company.php">
	<input type="hidden" id="displayTuplesRequest" name="displayTuplesRequest" class="styledButton">
		
	PostalCode: 
	<input type="text" name="PostalCodeSelection"> 
	<select name="PostalCodeCondition">
		<option value="AND">AND</option>
		<option value="OR">OR</option>
	</select>


	OfficeNum: 
	<input type="text" name="OfficeNumSelection"> 
	<select name="OfficeNumCondition">
		<option value="AND">AND</option>
		<option value="OR">OR</option>
	</select>


	Street: 
	<input type="text" name="StreetSelection">
	<select name="StreetCondition">
		<option value="AND">AND</option>
		<option value="OR">OR</option>
	</select>


	Name: 
	<input type="text" name="NameSelection"> 
	<select name="NameCondition">
		<option value="AND">AND</option>
		<option value="OR">OR</option>
	</select>


	CEO:
	 <input type="text" name="CEOSelection"> 
		<input type="submit" value="displayTuples" name="displayTuples", class="styledButton"></p>
	</form>

	<?php
	// The following code will be parsed as PHP

	

//////////////////////////////////////////// DB Code ///////////////////////////////////////////////////////

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

	function executeBoundSQL($cmdstr, $list)
	{
		/* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

		global $db_conn, $success;
		$statement = oci_parse($db_conn, $cmdstr);

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn);
			echo htmlentities($e['message']);
			$success = False;
		}

		foreach ($list as $tuple) {
			foreach ($tuple as $bind => $val) {
				//echo $val;
				//echo "<br>".$bind."<br>";
				oci_bind_by_name($statement, $bind, $val);
				unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
			}

			$r = oci_execute($statement, OCI_DEFAULT);
			if (!$r) {
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	}

	function connectToDB()
	{
		global $db_conn;
		global $config;

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

//////////////////////////////////////////// Selection Code ///////////////////////////////////////////////////////

	function handleDisplayRequest()
	{
		global $db_conn, $SQL_SANITIZATION_STRING;

		/* Quick check to see if user input any conditions */
		if (empty($_GET['PostalCodeSelection']) &&
			empty($_GET['OfficeNumSelection']) &&
			empty($_GET['StreetSelection']) &&
			empty($_GET['NameSelection']) &&
			empty($_GET['CEOSelection'])) {
			$result = executePlainSQL("SELECT * FROM Company");
			printResult($result);
			return;
		}

		if (!empty($_GET['OfficeNumSelection']) && (!is_numeric($_GET['OfficeNumSelection']) !== false)) {
			echo "Invalid format for Office num. Please enter an integer value.";
			return;
		}

		/* Get user conditions */
		$fields = array(
			'PostalCode' => 'PostalCodeCondition',
			'OfficeNum' => 'OfficeNumCondition',
			'Street' => 'StreetCondition',
			'Name' => 'NameCondition',
			'CEO' => 'CEOCondition'
		);

		/* Add each selection field to  array terminating with condition associated with field */
		foreach ($fields as $field => $conditionField) {
			/* CEO has no condition attched to it SPECIAL CASE */
			if ($field === 'CEO' && !empty($_GET[$field.'Selection'])) {
				//sanitization
				$sanitaziedselectionfield = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_GET[$field.'Selection']);
				$conditions[] = $field . " = '" .$sanitaziedselectionfield  . "' ";
			} elseif (!empty($_GET[$field.'Selection'])) {
				//sanitization
				$sanitaziedselectionfield = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_GET[$field.'Selection']);
				$sanitazedconditionfield = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_GET[$conditionField]);
				$conditions[] = $field . " = '" . $sanitaziedselectionfield . "' " .$sanitazedconditionfield  . " ";
			}
		}

		/* Construct the WHERE clause by iterating through conditions with a foreach loop */ 
		$whereClause = '';
		foreach ($conditions as $index => $condition) {
			$whereClause .= $condition;
		}
		/* Remove Extra condition generated at the end of the whereclause */
		$inputString = preg_replace('/\s+(AND|OR)\s*(?=\S*$)/', '', $whereClause);


		$result = executePlainSQL("SELECT * FROM Company WHERE ".$inputString);
		printResult($result);
	}


	function printResult($result)
	{ //prints results from a select statement
		echo "<br>Result: <br>";
		echo "<table>";
		echo "<tr><th>PostalCode ID</th><th>OfficeNum</th><th>Street
		</th><th>Name</th><th>CEO</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<tr><td>" . $row["POSTALCODE"] . "</td><td>" . $row["OFFICENUM"] . "</td><td>" . $row["STREET"]
			. "</td><td>" . $row["NAME"]. "</td><td>" . $row["CEO"] . "</td></tr>";
		}

		echo "</table>";
	}
//////////////////////////////////////////// Handler Code ///////////////////////////////////////////////////////

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handleGETRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('displayTuplesRequest', $_GET)) {
				handleDisplayRequest();
			} 

			disconnectFromDB();
		}
	}

	if (isset($_GET['displayTuplesRequest'])) {
		handleGETRequest();
	}
	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>



