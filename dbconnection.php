<?php

function dbconnection()
{
    // Establish connection to the database
    $con = mysqli_connect("localhost", "root", "", "hr");

    // Check connection
    if (!$con) {
        // Connection failed
        die("Connection failed: " . mysqli_connect_error());
    }

    // Return the connection object
    return $con;
}

// Call the function to establish connection
$db = dbconnection();

// No need to close the database connection here
// mysqli_close($db);

?>
