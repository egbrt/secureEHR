<?php
require_once 'config.php';

/**
* connect and open database
*
* @return bool true when connected to database
*/
function connectToDatabase($id, &$con)
{
    $valid = false;
    $con = mysqli_connect(DB_HOST,DBASES[$id][DB_USER],DBASES[$id][DB_PASSWORD]);
    if ($con) {
        mysqli_set_charset($con, 'utf8');
        if (mysqli_select_db($con, DBASES[$id][DB_NAME])) {
            $valid = true;
        }
        else {
            die('Could not select database: ' . mysqli_error($con));
        }
    }
    else {
        die('Could not connect, probably invalid user:' . mysqli_connect_error());
    }
    return $valid;
}


?>
