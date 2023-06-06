<?php

function getUserName($con, $user)
{
    $name = "unknown";
    $query = "SELECT * FROM users WHERE user=" . $user;
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            if ($row['fullName'] != "") {
                $name = $row['fullName'];
            }
            else {
                $name = $row['name'];
            }
        }
        mysqli_free_result($result);
    }
    return $name;
}

function getShortUserName($con, $user)
{
    $name = "unknown";
    $query = "SELECT * FROM users WHERE user=" . $user;
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            $name = $row['name'];
        }
        mysqli_free_result($result);
    }
    return $name;
}


function validateInput($text) {
  $text = trim($text);
  $text = stripslashes($text);
  $text = htmlspecialchars($text);
  return $text;
}



?>
