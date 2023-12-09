<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$config["dbuser"] = "ora_apiemont";
$config["dbpassword"] = "a90501727";
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;
$success = true;
$SQL_SANITIZATION_STRING = "'\";()"; 

//////////////////////////////////////////////////////// DB Code ///////////////////////////////////////////////////////


// Functions to connect and disconnect from the database
function connectToDB() {
    global $db_conn, $config;
    $db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);
    if ($db_conn) {
        return true;
    } else {
        $e = OCI_Error();
        echo htmlentities($e['message']);
        return false;
    }
}

function disconnectFromDB() {
    global $db_conn;
    oci_close($db_conn);
}

function executePlainSQL($cmdstr) {
    global $db_conn, $success;
    $statement = oci_parse($db_conn, $cmdstr);
    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = @oci_execute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement);
        echo htmlentities($e['message']);
        $success = False;
    }
    return $statement;
}

function executeBoundSQL($cmdstr, $bindValues) {
    global $db_conn, $success;
    $statement = oci_parse($db_conn, $cmdstr);
    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    foreach ($bindValues as $placeholder => $value) {
        oci_bind_by_name($statement, $placeholder, $value);
    }
    $r = @oci_execute($statement, OCI_DEFAULT);
    if (!$r) {
        $e = oci_error($statement);
        if ($e['code'] == 2292) {
            oci_rollback($db_conn);
            throw new Exception("Foreign key violation: " . htmlentities($e['message']), $e['code']);
        } else {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            echo htmlentities($e['message']);
            $success = False;
        }
    }
    return $statement;
}
//////////////////////////////////////////// Table initialiazion Code ///////////////////////////////////////////////////////

// Function to get all table names from the database
function getTableNames() {
    global $db_conn;
    $tables = array();
    $query = "SELECT table_name FROM user_tables";
    $statement = oci_parse($db_conn, $query);
    @oci_execute($statement);
    while ($row = oci_fetch_array($statement, OCI_ASSOC)) {
        $tables[] = $row['TABLE_NAME'];
    }
    return $tables;
}

// Function to get column names for a given table
function getColumnNames($tableName) {
    global $db_conn;
    $columns = array();
    $query = "SELECT column_name FROM user_tab_columns WHERE table_name = '".strtoupper($tableName)."'";
    $statement = oci_parse($db_conn, $query);
    @oci_execute($statement);
    while ($row = oci_fetch_array($statement, OCI_ASSOC)) {
        $columns[] = $row['COLUMN_NAME'];
    }
    return $columns;
}

//////////////////////////////////////////// Projection Code ///////////////////////////////////////////////////////


function handleProjectionRequest($tableName, $selectedColumns) {
    global $db_conn;
    if (!empty($selectedColumns)) {
        $columnsString = implode(", ", $selectedColumns);
        $result = executePlainSQL("SELECT $columnsString FROM $tableName");
        printProjectionResult($tableName, $result, $selectedColumns);
    } else {
        echo "No columns selected for projection in table $tableName";
    }
}
function printProjectionResult($tableName, $result, $selectedColumns) {
    echo "<br>Retrieved data from table $tableName:<br>";
    echo "<table>";
    echo "<tr>";
    foreach ($selectedColumns as $col) {
        echo "<th>$col</th>";
    }
    echo "</tr>";
    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        echo "<tr>";
        foreach ($selectedColumns as $col) {
            echo "<td>" . $row[strtoupper($col)] . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

//////////////////////////////////////////// Insert Code ///////////////////////////////////////////////////////

// Function to handle the insert operation
function handleInsertRequest($tableName, $data) {
    global $db_conn, $SQL_SANITIZATION_STRING;

    $columns = implode(", ", array_keys($data));
    $valuesArray = array_values($data);

    // Generate unique placeholders for each value
    $placeholders = [];
    foreach ($valuesArray as $index => $value) {
        $placeholders[] = ":value" . $index;
    }
    $placeholdersString = implode(", ", $placeholders);

    //Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
    $tableName  = str_replace(str_split($SQL_SANITIZATION_STRING), '', $tableName );
    $tournamentDate = str_replace(str_split($SQL_SANITIZATION_STRING), '', $columns);
    $placeholdersString = str_replace(str_split($SQL_SANITIZATION_STRING), '', $placeholdersString);

    $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholdersString)";
    $statement = oci_parse($db_conn, $sql);

    // Bind each value to its corresponding placeholder
    foreach ($placeholders as $index => $placeholder) {
        oci_bind_by_name($statement, $placeholder, $valuesArray[$index]);
    }

    // Execute the query
    if (@oci_execute($statement)) {
        echo "<p>Record inserted successfully into table $tableName.</p>";
    } else {
        $e = oci_error($statement);
        if ($e['code'] == 2291) {
            oci_rollback($db_conn);
            echo "Error: The specified foreign key does not exist, therefore insert failed.";
        } else if ($e['code'] == 1722) {
            oci_rollback($db_conn);
            echo "Error: You should enter a number, insert failed.";
        } else if ($e['code'] == 1) {
            oci_rollback($db_conn);
            echo "Error: This id already exists and cannot be entered again, insert failed.";
        } else if ($e['code'] == 1400) {
            oci_rollback($db_conn);
            echo "Error: Cannot insert empty value here, insert failed";
        } else {
            echo "<p>Error occurred: " . htmlentities($e['message']) . "</p>";
        }
    }
}

//////////////////////////////////////////// Update Code ///////////////////////////////////////////////////////


// Function to handle the update operation
function handleUpdateRequest($tableName, $conditions, $newValues) {
    global $db_conn, $SQL_SANITIZATION_STRING;

    // Prepare update string with named placeholders
    $updateParts = [];
    foreach ($newValues as $column => $value) {
        if ($value !== null && $value !== '') {
            $updateParts[] = "$column = :new_$column";
        }
    }
    $updateString = implode(", ", $updateParts);

    if (empty($updateParts)) {
        echo "No values provided for update.";
        return;
    }

    // Prepare conditions string with named placeholders
    $conditionParts = [];
    foreach ($conditions as $column => $value) {
        if ($value !== null && $value !== '') {
            $conditionParts[] = "$column = :cond_$column";
        }
    }
    $conditionString = implode(" AND ", $conditionParts);

     //Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
     $tableName  = str_replace(str_split($SQL_SANITIZATION_STRING), '', $tableName );
     $updateString  = str_replace(str_split($SQL_SANITIZATION_STRING), '', $updateString );
     $conditionString= str_replace(str_split($SQL_SANITIZATION_STRING), '', $conditionString);

    // Combine the query
    $sql = "UPDATE $tableName SET $updateString WHERE $conditionString";
    $statement = oci_parse($db_conn, $sql);

    // Bind the new values to the statement
    foreach ($newValues as $column => $value) {
        if ($value !== null && $value !== '') {
            @oci_bind_by_name($statement, ":new_$column", $newValues[$column]);
        }
    }

    // Bind the condition values to the statement
    foreach ($conditions as $column => $value) {
        @oci_bind_by_name($statement, ":cond_$column", $conditions[$column]);
    }

    //for bebugging
    // echo "SQL Query: $sql<br>";
    // echo "New Values: "; print_r($newValues); echo "<br>";
    // echo "Conditions: "; print_r($conditions); echo "<br>";

    // Execute the query
    if (@oci_execute($statement)) {
        echo "<p>Record updated successfully in table $tableName.</p>";
    } else {
        $e = oci_error($statement);
        echo "<p>Error occurred: " . htmlentities($e['message']) . "</p>";
    }
}

//////////////////////////////////////////// Delete Code ///////////////////////////////////////////////////////

// function to handle delete operation
function handleDeleteRequest($tableName, $conditions) {
    global $db_conn, $SQL_SANITIZATION_STRING;

    $conditionParts = [];
    $bindValues = [];
    foreach ($conditions as $column => $value) {
        if ($value !== null && $value !== '') {
            $conditionParts[] = "$column = :$column";
            $bindValues[":$column"] = $value;
        }
    }

    if (empty($conditionParts)) {
        echo "No conditions provided for deletion";
        return;
    }
    $conditionString = implode(" AND ", $conditionParts);

    //Sanitize user input using $SQL_SANITIZATION_STRING, which removes all problematic characters
    $conditionString  = str_replace(str_split($SQL_SANITIZATION_STRING), '', $conditionString);
    $tableName = str_replace(str_split($SQL_SANITIZATION_STRING), '', $tableName);

    $sql = "DELETE FROM $tableName WHERE $conditionString";

    // Debugging output
    try {
        $result = executeBoundSQL($sql, $bindValues);
        if ($result) {
            oci_commit($db_conn); // Commit the transaction
            echo "<p>Record deleted successfully from table $tableName.</p>";
        }
    } catch (Exception $e) {
        if ($e->getCode() == 2292) {
            echo "Error: Cannot delete the record due to a foreign key constraint.";
        } else {
            echo "<p>Error in delete operation: " . htmlentities($e['message']) . "</p>";
        }
    }
}

//////////////////////////////////////////// Handler Code ///////////////////////////////////////////////////////

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (connectToDB()) {
        if (isset($_POST['projectColumnsRequest'])) {
            $tableName = $_POST['tableName'];
            $selectedColumns = $_POST['columns'];
            handleProjectionRequest($tableName, $selectedColumns);
        } else if (isset($_POST['insertRequest'])) {
            handleInsertRequest($_POST['tableName'], $_POST['insertData']);
        } else if (isset($_POST['updateRequest'])) {
            handleUpdateRequest($_POST['tableName'], $_POST['updateConditions'], $_POST['updateValues']);
        } else if (isset($_POST['deleteRequest'])) {
            handleDeleteRequest($_POST['tableName'], $_POST['deleteConditions']);
        }
        disconnectFromDB();
    }
}

?>
<html>
<head>
    <title>Database Projection Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 20px;
            padding: 20px;
        }

        h2 {
            color: #0066cc;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="submit"] {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        select {
            width: 200px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .tableForm {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>

    <script>
    function showSelectedTableForm(tableName, formType) {
        console.log('Selected Table:', tableName, 'Form Type:', formType); // Debugging

        var forms = document.getElementsByClassName('tableForm');
        for (var i = 0; i < forms.length; i++) {
            forms[i].style.display = 'none'; // Hide all forms
        }

        var formId = tableName + '_' + formType;
        var selectedForm = document.getElementById(formId);
        if (selectedForm) {
            selectedForm.style.display = 'block';
        } else {
            console.log('Form not found:', formId); // Debugging
        }
    }



    function changeTable(tableName) {
        // Hide all buttons
        var buttonContainers = document.querySelectorAll("[id^='buttons_']");
        buttonContainers.forEach(function(container) {
            container.style.display = 'none';
        });

        // Show the buttons for the selected table
        if (tableName) {
            var buttons = document.getElementById('buttons_' + tableName);
            if (buttons) {
                buttons.style.display = 'block';
            }
        }

        // Show the insert form by default
        showSelectedTableForm(tableName, 'form');
    }
</script>

</head>
<body>
    <h2>Database Tables Projection Interface</h2>
    <form method="POST" action="homepage.php">
        <p><input type="submit" value="Back" name="reset"></p>
    </form>

    <!-- Dropdown to select table -->
    <form id="tableSelectForm">
    <select id="tableSelect" onchange="changeTable(this.value)">
            <option value="">Select a table</option>
            <?php
                if (connectToDB()) {
                    $tableNames = getTableNames();
                    foreach ($tableNames as $tableName) {
                        echo "<option value='".$tableName."'>".$tableName."</option>";  
                        
                    }
                    disconnectFromDB();
                }
            ?>
        </select>
    </form>
            

    <div id="tableFormContainer">
        <?php
            if (connectToDB()) {
                $tableNames = getTableNames();
                foreach ($tableNames as $tableName) {
                    $columns = getColumnNames($tableName);

                    // Buttons for each table
                    echo "<div id='buttons_$tableName' style='display:none;'>";
                    echo "<button onclick=\"showSelectedTableForm('$tableName', 'form')\">Insert</button>";
                    echo "<button onclick=\"showSelectedTableForm('$tableName', 'update')\">Update</button>";
                    echo "<button onclick=\"showSelectedTableForm('$tableName', 'delete')\">Delete</button>";
                    echo "<button onclick=\"showSelectedTableForm('$tableName', 'project')\">Project</button>";
                    echo "</div>";
                    //  insert form
                    echo "<div id='" . $tableName . "_form' class='tableForm' style='display:none;'>";
                    echo "<h2>Insert into " . $tableName . "</h2>";
                    echo "<form method='POST' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>";
                    echo "<input type='hidden' name='tableName' value='" . $tableName . "'>";
                    echo "<input type='hidden' name='insertRequest' value='1'>";
                    foreach ($columns as $column) {
                        echo $column . ": <input type='text' name='insertData[" . $column . "]'><br>";
                    }
                    echo "<input type='submit' value='Insert'>";
                    echo "</form>";
                    echo "</div>";

                    // Update form
                    echo "<div id='" . $tableName . "_update' class='tableForm' style='display:none;'>";
                    echo "<h2>Update " . $tableName . "</h2>";
                    echo "<form method='POST' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>";
                    echo "<input type='hidden' name='tableName' value='" . $tableName . "'>";
                    echo "<input type='hidden' name='updateRequest' value='1'>";

                    echo "<h3>Conditions (Specify which record to update):</h3>";
                    foreach ($columns as $column) {
                        echo $column . " (condition): <input type='text' name='updateConditions[" . $column . "]'><br>";
                    }

                    echo "<h3>New Values:</h3>";
                    foreach ($columns as $column) {
                        echo $column . " (new value): <input type='text' name='updateValues[" . $column . "]'><br>";
                    }
                    
                    echo "<input type='submit' value='Update'>";
                    echo "</form>";
                    echo "</div>";

                    // Delete form
                    echo "<div id='" . $tableName . "_delete' class='tableForm' style='display:none;'>";
                    echo "<h2>Delete from " . $tableName . "</h2>";
                    echo "<form method='POST' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>";
                    echo "<input type='hidden' name='tableName' value='" . $tableName . "'>";
                    echo "<input type='hidden' name='deleteRequest' value='1'>";

                    foreach ($columns as $column) {
                        echo $column . ": <input type='text' name='deleteConditions[" . $column . "]'><br>";
                    }
                    
                    echo "<input type='submit' value='Delete'>";
                    echo "</form>";
                    echo "</div>";

                     // Projection form
                    echo "<div id='" . $tableName . "_project' class='tableForm' style='display:none;'>";
                    echo "<h2>Project Columns from " . $tableName . "</h2>";
                    echo "<form method='POST' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>";
                    echo "<input type='hidden' name='tableName' value='" . $tableName . "'>";
                    echo "<input type='hidden' name='projectColumnsRequest' value='1'>";
                    foreach ($columns as $column) {
                        echo "<input type='checkbox' name='columns[]' value='" . $column . "'> " . $column . "<br>";
                    }
                    echo "<input type='submit' value='Project Columns'>";
                    echo "</form>";
                    echo "</div>";
                }
                disconnectFromDB();
            }
        ?>
    </div>

</body>
</html>
