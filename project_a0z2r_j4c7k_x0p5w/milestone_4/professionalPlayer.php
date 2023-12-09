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
$config["dbuser"] = "ora_janayman";			
$config["dbpassword"] = "a75867630";	
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()
$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>

<html>

<head>
	<title>CPSC 304 Milestone 4</title>
</head>

<body>
	<h2>CPSC 304 Professional Player Table</h2>
	<p>Back to Home Page </p>

	<form method="POST" action="homepage.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" value="Back" name="reset"></p>
	</form>

	<hr />

	<h2>Insert Values into Professional Player Table</h2>
	<p>PlayerID must be unique </p>
	<form method="POST" action="professionalPlayer.php">
		<input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
		PlayerID: <input type="text" name="PlayerID"> 
		Rank: <input type="text" name="Rank"> 

		<input type="submit" value="Insert" name="insertSubmit"></p>
	</form>

	<hr />

	<h2>Delete Values into Professional Player Table</h2>
	<p>Enter ALL</p>
	<form method="POST" action="professionalPlayer.php">
		<input type="hidden" id="DeleteQueryRequest" name="DeleteQueryRequest">
		PlayerID: <input type="text" name="PlayerID"> 
		<input type="submit" value="Delete" name="DeleteSubmit"></p>
	</form>

	<hr />

	<h2>Update Values into Professional Player Table</h2>
	<p>Primary Keys can not be updated, primary keys are used to idenfy tuple that needs to be updated</p>
	<p>ALL fields are updated even if left blank, if some information should remain the same then re-enter old value</p>
	<form method="POST" action="professionalPlayer.php">
		<input type="hidden" id="UpdateQueryRequest" name="UpdateQueryRequest">
		PlayerID(Primary Key): <input type="text" name="PlayerID"> 
		New Rank: <input type="text" name="newRank"> 
		<input type="submit" value="Update" name="UpdateSubmit"></p>
	</form>

	<hr />

	<h2>Display Tuples in Professional Player Table</h2>
	<p>Enter ALL fields, otherwise all tuples are going to be displayed</p>
	<form method="GET" action="professionalPlayer.php">
		<input type="hidden" id="displayTuplesRequest" name="displayTuplesRequest">
		PlayerID: <input type="text" name="PlayerID"> 
		<input type="submit" value="AllTuples" name="displayTuples"></p>
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

			$r = @oci_execute($statement, OCI_DEFAULT);
			if (!$r) {
				$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
				if ($e['code'] == 2291) {
					oci_rollback($db_conn);
					throw new Exception("Foreign key violation: " . htmlentities($e['message']), $e['code']);
				} else {
					echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
					echo htmlentities($e['message']);
					echo "<br>";
					$success = False;
				}
			}
		}
	}

	function printResult($result)
	{ //prints results from a select statement
		echo "<br>Retrieved data from table Professional Player Table:<br>";
		echo "<table>";
		echo "<tr><th>Player ID</th><th>Rank</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<tr><td>" . $row["PLAYERID"] . "</td><td>" . $row["RANK"] . "</td></tr>";	
		}

		echo "</table>";
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

	function handleInsertRequest()
	{
		global $db_conn;

		try {
			//Getting the values from user and insert data into the table
			$tuple = array(
				":bind1" => $_POST['PlayerID'],
				":bind2" => $_POST['Rank']
			);

			$alltuples = array(
				$tuple
			);

			executeBoundSQL("insert into ProfessionalPlayer values (:bind1, :bind2)", $alltuples);
			oci_commit($db_conn);
		} catch (Exception $e) {
			// To handle the foreign key violation exception
			if ($e->getCode() == 2291) {
				// foreign key violation error
				echo "Error: The specified PlayerID does not exist in the Players table";
			} else {
				// For other errors:
				echo "Error: " . $e->getMessage();
			}
		}
	}

	function deleteInsertRequest()
	{
		global $db_conn;

		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind1" => $_POST['PlayerID']
		);

		$alltuples = array(
			$tuple
		);

		executeBoundSQL("DELETE FROM ProfessionalPlayer WHERE PlayerID = :bind1", $alltuples);
		oci_commit($db_conn);
	}

	function handleUpdateRequest()
	{
		global $db_conn;

		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind1" => $_POST['PlayerID'],
			":bind2" => $_POST['newRank']
		);

		$alltuples = array(
			$tuple
		);

		// you need the wrap the old name and new name values with single quotations
		executeBoundSQL("UPDATE ProfessionalPlayer SET Rank = :bind2 WHERE PlayerID = :bind1", $alltuples);
		oci_commit($db_conn);
	}

	function handleDisplayRequest()
	{
		global $db_conn;


		if ($_GET['PlayerID'] == ''){
			$result = executePlainSQL("SELECT * FROM ProfessionalPlayer");
		} else {
			$playerID = intval($_GET['PlayerID']);
			$result = executePlainSQL("SELECT * FROM ProfessionalPlayer WHERE PLAYERID = $playerID");
		}
	
		printResult($result);
	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('insertQueryRequest', $_POST)) {
				handleInsertRequest();
			} else if (array_key_exists('DeleteQueryRequest', $_POST)) {
				deleteInsertRequest();
			} else if (array_key_exists('UpdateQueryRequest', $_POST)) {
				handleUpdateRequest();
			} 

			disconnectFromDB();
		}
	}

	// HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handleGETRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('displayTuples', $_GET)) {
				handleDisplayRequest();
			}

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset']) || isset($_POST['updateSubmit']) 
	|| isset($_POST['insertSubmit'])|| isset($_POST['DeleteSubmit'])
	|| isset($_POST['UpdateSubmit'])) {
		handlePOSTRequest();
	} else if (isset($_GET['displayTuplesRequest'])) {
		handleGETRequest();
	}

	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>


