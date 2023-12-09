<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values

  This code was created on top of the Test Oracle file for UBC CPSC304
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
	<title>CPSC 304 Milestone 4</title>
	<style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }

		h1 {
            background-color: #2196F3; /* Light blue background color for h2 */
            color: white; /* White text color */
            padding: 15px 0 10px; /* Padding above, below, and below the text */
            margin: 0; /* Remove default margin */
            text-align: center; /* Center the text */
        }


        form {
            margin-bottom: 20px;
        }

		input[type=text] {
  			width: 100%;
  			padding: 12px 20px;
  			margin: 8px 0;
  			box-sizing: border-box;
			font-size: 16px;
		}

        input[type="submit"] {
            background-color: #4CAF50; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }

        input[type="submit"]:hover {
            background-color: #45a049; 
        }

        hr {
            margin-top: 40px;
            border: 1px solid #ddd;
        }

		table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
			background-color: #2196F3;
        }

        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
	<form method="POST" action="homepage.php">
	<h1>Advanced Search Page</h2>
		<input type="submit" value="Back">
	</form>

	<!-- User Interface for Demo-ing the Query: Join-->
	<h2>Finds all players in a given tournament</h2>
	<p> Data format should be: YYYY-MM-DD </p>
	<form method="POST" action= "advancedqueries.php">
		<input type="hidden" id="JoinQuery" name="JoinQuery">
		TournamentName(Key): <input type="text" name="TournamentName"> 
		TournamentDate(Key): <input type="text" name="TournamentDate"> 
		<input type="submit" value="Search" name="PostQuery"></p>
	</form>


	<!-- User Interface for Demo-ing the Query: Aggregation with GROUP BY-->
	<h2>Finds the number of games on each platform</h2>
	<form method="POST" action="advancedqueries.php">
	<input type="hidden" id="groupByQuery" name="groupByQuery">
	PlatformType: <input type="text" name="PlatformType">
		<input type="submit" value="Search" name="PostQuery"></p>
	</form>
	<form method="POST" action= "advancedqueries.php">
		To insert new values into the platforms table: 
		<input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
		PlatformType(Key): <input type="text" name="PlatformType"> 
		GameID(Key): <input type="text" name="GameID"> 
		<input type="submit" value="Insert" name="PostQuery"></p>
	</form>

	<!-- User Interface for Demo-ing the Query: Aggregation with HAVING-->
	Aggregation with GROUP BY
	<h2>Find the average rating of games above a threshold</h2>
	<form method="POST" action="advancedqueries.php">
	Input a rating score to set the threshold 
	<input type="hidden" id="groupByHavingQuery" name="groupByHavingQuery">
	RatingScore: <input type="text" name="RatingScore">
		<input type="submit" value="Search" name="PostQuery"></p>
	</form>
	<p>Note: combinations of keys MUST be unique</p>
	<form method="POST" action= "advancedqueries.php">
	Insert new values for rating table:
	<input type="hidden" id="insertReviewQueryRequest" name="insertReviewQueryRequest">
		ReviewID(Key): <input type="text" name="ReviewID"> 
		PlayerID(Key): <input type="text" name="PlayerID"> 
		GameID(Key): <input type="text" name="GameID"> 
		RatingScore: <input type="text" name="RatingScore"> 
		<input type="submit" value="Insert" name="PostQuery"></p>
	</form>
	
	<!-- User Interface for Demo-ing the Query:  Nested Aggregation with GROUP BY-->
	<h2>Find All Players Who are Yet to Win a Tournament given a time frame</h2>
	<p>If no time frame is given, it will show all players who have never won a tournament</p>
    <form method="POST" action="advancedqueries.php">
        <input type="hidden" id="winnersQuery" name="winnersQuery">
		DateStart: <input type="text" name="DateStart">
		DateEnd: <input type="text" name="DateEnd">
        <input type="submit" value="Search" name="PostQuery"></p>
    </form>
	<hr />

	<!-- User Interface for Demo-ing the Query:  Division-->
	<h2>Find Average Number of Participants for Games Made by a Specific Company</h2>
    <form method="POST" action="advancedqueries.php">
        <input type="hidden" id="avgParticipantsQuery" name="avgParticipantsQuery">
        Company Name: <input type="text" name="companyName">
        <input type="submit" value="Search" name="PostQuery"></p>
    </form>
    <hr />

	<!-- User Interface for Demo-ing the Query:  Division-->
	<h2>Find Players who were in all tournaments</h2>
    <form method="POST" action="advancedqueries.php">
        <input type="hidden" id="inAllTournaments" name="inAllTournaments">
        <input type="submit" value="Search" name="PostQuery"></p>
    </form>
    <hr />

	<?php
	// The following code will be parsed as PHP


	////////////////////////////////////////////////////DB CODE////////////////////////////////////////////////////////////////
	/**
	 * Debug Alert Message Function
	 *
	 * This function displays a JavaScript alert message for debugging purposes,
	 * if the global variable $show_debug_alert_messages is set to true.
	 * The alert message is generated using the provided $message parameter.
	 *
	 */
	function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/javascript'>alert('" . $message . "');</script>";
		}
	}

	/**
	 * Execute Plain SQL Function
	 *
	 * This function takes a plain SQL command (with no bound variables), prepares and executes it using
	 * the Oracle. It handles parsing and execution, checking for errors, and
	 * returns the statement handle for further processing if needed.
	 *
	 */
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

	/**
	 * Execute Bound SQL Function
	 *
	 * This function prepares and executes a SQL command with bound variables using
	 * the Oracle. It handles parsing, binding variables, and execution
	 * for repeated statements with different variable values. .
	 */
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
				oci_bind_by_name($statement, $bind, $val);
				unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
			}
			$r = @oci_execute($statement, OCI_DEFAULT);
			//$r = oci_execute($statement, OCI_DEFAULT); REPLACE FOR DEBUGGING
			if (!$r) {

				$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle

				// Check if the error message is because of "integrity constraint violated - parent key not found"
				if (strpos(htmlentities($e['message']), 'parent key not found') !== false) {
					echo "<strong>ERROR: This table reference information refers to other table(s) information. Make sure the information is in other tables first before:</strong><br>"
					. "<strong>- In table TournamentWasAbout:</strong> information should be added first to GameSeriesMadeBy.<br>"
					. "<strong>- In table PlayedOn:</strong> information should be added first to TournamentWasAbout and Player.<br>"
					. "<strong>- In table Classification:</strong> information should be added first to Genres and GameSeriesMadeBy.<br>"
					. "<strong>- In table PlaysOn:</strong> information should be added first to Platform and GameSeriesMadeBy.<br>"
					. "<strong>- In table Play:</strong> information should be added first to Player and GameSeriesMadeBy.<br>"
					. "<strong>- In table GameEditionHave:</strong> information should be added first to GameSeriesMadeBy.<br>"
					. "<strong>- In table ReviewEvaluates:</strong> information should be added first to Player, GameSeriesMadeBy, and ReviewEvaluatesRating.<br>"
					. "Check the relationships and ensure that the referenced information exists in the corresponding tables.";
					$success = False;
					return;
				}
				// Check if the error message is because of "integrity constraint violated - parent key not found"
				if (strpos(htmlentities($e['message']), 'unique constraint') !== false) {
					echo "<strong>ERROR DUPLICATE:</strong> Tuple already exitis on the table!! ";
					$success = False;
					return;
				}
				
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	}

	/**
	 * Connect to data base
	 */
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

	/**
	 * disconnect from data base
	 */
	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

	////////////////////////////////////////////////////QUERY CODE////////////////////////////////////////////////////////////////
	

	/**
	 * Demo-ing the Query: Join
	 * This functions finds all players in a given tournament 
	 */
	function handleJoinQueryRequest()
	{
		global $db_conn, $SQL_SANITIZATION_STRING;
	
		//Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
		$tournamentName = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['TournamentName']);
		$tournamentDate = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['TournamentDate']);

		//Check user input for proper format
		if (DateTime::createFromFormat('Y-m-d', $tournamentDate) === false) {
			echo "Invalid date format for TournamentDate";
			return;
		} 

		// Execute SQL query
		$result = executePlainSQL("SELECT DISTINCT P.Name 
		FROM Player P, PlayedOn PO 
		WHERE PO.TournamentName = '".$tournamentName. "' 
		AND PO.TournamentDate = TO_DATE('".$tournamentDate."','YYYY-MM-DD') 
		AND PO.PlayerID = P.PlayerID");

		// Print query results and commit changes to the database
		printResult($result, 'JoinQuery');
		oci_commit($db_conn);
	}


	/**
	 * Demo-ing the Query: Aggregation with GROUP BY
	 * Finds the number of games on each platform, or all plarforms if a plataform is not given
	 */
	function handleGroupByRequest()
	{
		global $db_conn, $SQL_SANITIZATION_STRING;
	
		//Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
		$platformtype = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['PlatformType']);


		// Execute SQL query
		if ($platformtype == '') {
			$result = executePlainSQL("SELECT Count(*), PlatformType 
									   FROM PlaysOn 
									   GROUP BY PlatformType");
		} else {
			$result = executePlainSQL("SELECT Count(*), PlatformType 
									   FROM PlaysOn 
									   WHERE PlatformType = '".$platformtype. "' 
									   GROUP BY PlatformType");
		}

		// Print query results and commit changes to the database
		printResult($result, 'GroupByQuery');
		oci_commit($db_conn);
	}

	/**
	 * Demo-ing the Query: Aggregation with HAVING
	 * Find the average rating of games above a threshold
	 */
	function handleGroupByHavingQueryRequest() {
		global $db_conn, $SQL_SANITIZATION_STRING;

		//Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
		$ratingscore = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['RatingScore']);

		 // Check if the rating score is numeric
		 if (!is_numeric($ratingscore) !== false) {
			echo "Invalid format for RatingScore. Please enter an integer value.";
			return;
		}
	
		// Execute SQL query
		$result = executePlainSQL("
			SELECT g.gameId, AVG(r.ratingScore) AS AverageRating
			FROM GameSeriesMadeBy g, ReviewEvaluates r
			WHERE g.gameId = r.gameId
			GROUP BY g.gameId
			HAVING AVG(r.ratingScore) > '".$ratingscore. "'
		");
	
		// Print query results and commit changes to the database
		printResult($result, 'GroupByHavingQuery');
		oci_commit($db_conn);
	}

	/**
	 * Demo-ing the Query: Nested Aggregation with GROUP BY
	 * Find Average Number of Participants for Games Made by a Specific Company
	 */
	function handleAvgParticipantsQueryRequest() {
        global $db_conn, $SQL_SANITIZATION_STRING;

		//Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
        $companyName = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['companyName']);;

		// Execute SQL query
        $result = executePlainSQL("
            SELECT T.GameID, AVG(T.ParticipantsNum) AS AvgParticipantsPerGame 
            FROM TournamentWasAbout T 
            WHERE T.GameID IN (
                SELECT GSM.GameID 
                FROM GameSeriesMadeBy GSM 
                WHERE GSM.PostalCode IN (
                    SELECT C.PostalCode 
                    FROM Company C 
                    WHERE C.Name = '$companyName'
                )
            ) 
            GROUP BY T.GameID
        ");

		// Print query results and commit changes to the database
		printResult($result, 'avgParticipantsQuery');
        oci_commit($db_conn);
    }

	function handleInAllTournaments() {
        global $db_conn, $SQL_SANITIZATION_STRING;


		// Execute SQL query
        $result = executePlainSQL("
		SELECT PlayerID, Name 
		FROM Player P 
		WHERE NOT EXISTS (SELECT 1 
						FROM TournamentWasAbout T 
						WHERE NOT EXISTS (SELECT 1 
						FROM PlayedOn PO 
						WHERE PO.PlayerID = P.PlayerID AND 
							PO.TournamentName = T.TournamentName AND 
							PO.TournamentDate = T.TournamentDate))

        ");

		// Print query results and commit changes to the database
		printResult($result, 'inAllTournaments');
        oci_commit($db_conn);
    }


	/**
	 * 	Demo-ing the Query: Division
	 *  Find All Players Who are Yet to Win a Tournament
	 */
	function handleWinnersQueryRequest() {
        global $db_conn, $SQL_SANITIZATION_STRING;

		if (empty($_POST['DateStart'])&&empty($_POST['DateEnd'])){
			$result = executePlainSQL("
			SELECT PlayerID, Name 
			FROM Player 
			MINUS 
			SELECT PlayerID, Name 
			FROM Player P, TournamentWasAbout T 
			WHERE P.Name = T.Winner
			");
		} else {
			$startdate = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['DateStart']);
			$enddate = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['DateEnd']);

			//Check user input for proper format
			if (DateTime::createFromFormat('Y-m-d', $startdate) === false ||
				DateTime::createFromFormat('Y-m-d', $enddate) === false) {
				echo "Invalid date format for TournamentDate";
				return;
			} 

			$result = executePlainSQL("
			SELECT P.PlayerID, P.Name 
			FROM Player P
			MINUS 
			SELECT PL.PlayerID, PL.Name 
			FROM Player PL, TournamentWasAbout T
			WHERE PL.Name = T.Winner AND T.TournamentDate >= TO_DATE('".$startdate."','YYYY-MM-DD') 
			AND T.TournamentDate <= TO_DATE('".$enddate."','YYYY-MM-DD')
			");
		}

        printResult($result, 'winnersQuery');
        oci_commit($db_conn);
    }

	 ////////////////////////////////////////////////////INSERT CODE////////////////////////////////////////////////////////////////
	/**
	 * handlePlatformInsertRequest Function
	 *
	 * This function handles the insertion of data into the PlaysOn table
	 * for the association between a platform and a game. It retrieves values
	 * from the user input, sanitizes them, prepares the data, and inserts
	 * it into the PlaysOn table.
	 */
	function handlePlatformInsertRequest()
	{
		global $db_conn, $SQL_SANITIZATION_STRING;

		
		// Get the values from user input and sanitize them
		$platformtype = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['PlatformType']);
		$gameid = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['GameID']);

		 // Check if the rating score is numeric
		 if (!is_numeric($gameid) !== false) {
			echo "Invalid format for GameID. Please enter an integer value.";
			return;
		}
	
		// Prepare the data for insertion
		$tuple = array(
			":bind1" => $platformtype,
			":bind2" => $gameid
		);

		// Create an array of tuples
		$alltuples = array(
			$tuple
		);

		// Execute the bound SQL query to insert data into the PlaysOn table
		executeBoundSQL("insert into PlaysOn values (:bind1, :bind2)", $alltuples);

		 // Commit the changes to the database
		oci_commit($db_conn);
	}


	/**
	 * handleInsertReviewRequest Function
	 *
	 * This function handles the insertion of data into the ReviewEvaluates table 
	 * It retrieves values from the user input, sanitizes them, prepares the data, and inserts
	 * it into the ReviewEvaluates table.
	 */
	function handleInsertReviewRequest()
	{
		global $db_conn, $SQL_SANITIZATION_STRING;

		// Get the values from user input and sanitize them
		$reviewid = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['ReviewID']);
		$playerid = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['PlayerID']);
		$gameid = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['GameID']);
		$ratingscore = str_replace(str_split($SQL_SANITIZATION_STRING), '', $_POST['RatingScore']);

		// Prepare the data for insertion
		$tuple = array(
			":bind1" => $reviewid,
			":bind2" => $playerid,
			":bind3" => $gameid,
			":bind4" => $ratingscore
		);

		// Create an array of tuples
		$alltuples = array(
			$tuple
		);

		// Execute the bound SQL query to insert data into the PlaysOn table
		executeBoundSQL("insert into ReviewEvaluates values (:bind1, :bind2, :bind3, :bind4)", $alltuples);

		// Commit the changes to the database
		oci_commit($db_conn);
	}
	 ////////////////////////////////////////////////////QUERY HELPER CODE////////////////////////////////////////////////////////////////
	/**
	 * handlePOSTRequest Function
	 *
	 * This function handles the incoming POST requests and delegates
	 * the processing to specific functions based on the received parameters.
	 * It connects to the database, processes the request, and disconnects from the database.
	 *
	 * @return void
	 */
	function handlePOSTRequest()
	{
		// Check if the connection to the database is successful
		if (connectToDB()) {
			// Check for specific POST parameters and delegate processing accordingly
			
			if (array_key_exists('JoinQuery', $_POST)) {
				handleJoinQueryRequest();
			} 
			else if (array_key_exists('groupByQuery', $_POST)) {
				handleGroupByRequest();
			} 
			else if (array_key_exists('groupByHavingQuery', $_POST)){
				handleGroupByHavingQueryRequest();
			} 
			else if (array_key_exists('insertQueryRequest', $_POST)) {
				handlePlatformInsertRequest();
			} 
			else if (array_key_exists('insertReviewQueryRequest', $_POST)) {
				handleInsertReviewRequest();
			} 
			else if (array_key_exists('winnersQuery', $_POST)) {
				handleWinnersQueryRequest();
			} 
			else if (array_key_exists('avgParticipantsQuery', $_POST)) {
				handleAvgParticipantsQueryRequest();  // Handling the new query request
			} else if (array_key_exists('inAllTournaments', $_POST)) {
				handleInAllTournaments();
			}

			// Disconnect from the database after processing the request
			disconnectFromDB();
		}
	}
	/**
	 * This function SQL search result from SQL query, or "No results found." if 
	 * SQL search returned empty
	 * @param $result, query search returned from SQL database
	 * @param $queryType, type of search
	 */
	function printResult($result, $queryType)
	{
		// Fetch all rows into an associative array
		$rows = oci_fetch_all($result, $resultArray, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
	
		// Check if the result is empty
		if ($rows === 0) {
			echo "No results found.";
			return;
		}
	
		echo "<br>Retrieved data: </br>";
		echo "<table>";
	
		// Adjust the output based on the $queryType
		if ($queryType === 'JoinQuery') {
			echo "<tr><th>Name</th></tr>";
			foreach ($resultArray as $row) {
				echo "<tr><td>" . $row["NAME"] . "</td></tr>";
			}
		} else if ($queryType === 'GroupByQuery') {
			echo "<tr><th>Count</th><th>Platform Type</th></tr>";
			foreach ($resultArray as $row) {
				echo "<tr><td>" . $row["COUNT(*)"] . "</td><td>" . $row["PLATFORMTYPE"] . "</td></tr>";
			}
		} else if ($queryType === 'GroupByHavingQuery') {
			echo "<tr><th>Game ID</th><th>Average Rating Score</th></tr>";
			foreach ($resultArray as $row) {
				echo "<tr><td>" . $row["GAMEID"] . "</td><td>" . $row["AVERAGERATING"] . "</td></tr>";
			}
		} else if ($queryType === 'avgParticipantsQuery') {
			echo "<tr><th>Game ID</th><th>Average Participants Per Game</th></tr>";
			foreach ($resultArray as $row) {
				echo "<tr><td>" . $row["GAMEID"] . "</td><td>" . $row["AVGPARTICIPANTSPERGAME"] . "</td></tr>";
			}
		} else if ($queryType === 'winnersQuery') {
			echo "<tr><th>Player ID</th><th>Player Name</th>></tr>";
			foreach ($resultArray as $row) {
				echo "<tr><td>" . $row["PLAYERID"] . "</td><td>" . $row["NAME"] . "</td></tr>";
			}
		} else if ($queryType === 'inAllTournaments') {
			echo "<tr><th>Player ID</th><th>Player Name</th>></tr>";
			foreach ($resultArray as $row) {
				echo "<tr><td>" . $row["PLAYERID"] . "</td><td>" . $row["NAME"] . "</td></tr>";
			}
		}
	
		echo "</table>";
	}

	if (isset($_POST['PostQuery'])) {
		handlePOSTRequest();
	} 


	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>


    

