<?php
require_once 'login.php';
startSecureSession();
require_once 'config.php';
require_once "../classes/table.php";
?>

<!DOCTYPE html>
<html>
<head>
    <?php include "../templates/title.html" ?>
	<link type="text/css" rel="stylesheet" href="./auth.css" />
    <link type="text/css" rel="stylesheet" href="../styles/main.css" />
</head>
<body>

<?php
include "../templates/head.html";

if (isset($_REQUEST['dbase'])) {
    echo "dbase= " . $_REQUEST['dbase'];
    if (connectToDatabase($_REQUEST['dbase'], $con)) {
        if (isset($_POST['userName']) and isset($_POST['userPass'])) {
            $name = $_POST['userName'];
            $fullName = $_POST['fullName'];
            $pass = $_POST['userPass'];
            $email = $_POST['userEmail'];
            $role = $_POST['userRole'];
            if (addNewUser($con, $name, $fullName, $pass, $email, $role)) {
                header('Location: ../index.php');
            }
            else {
                echo "something went wrong, please try again.";
            }
        }
        else {    
            createTable($con);
            echo "<div class=\"newUser\">";
            echo "<form method=\"post\">";
            echo "<table><tr><th colspan=2>Setup administrator</th></tr>";
            echo "<tr><td>Login name</td>";
            echo "<td><input type=\"text\" id=\"userName\" name=\"userName\" autofocus=\"autofocus\" size=\"20\" value=\"admin\" /></td></tr>";
            echo "<tr><td>Email</td>";
            echo "<td><input type=\"email\" id=\"userEmail\" name=\"userEmail\" size=\"20\" /></td></tr>";
            echo "<tr><td>Password</td>";
            echo "<td><input type=\"password\" id=\"userPass\" name=\"userPass\" size=\"20\" /></td></tr>";
            echo "<tr><td>Role</td>";
            echo "<td><select id=\"userRole\" name=\"userRole\"><option value=\"admin\">admin</option>";
            echo "</select></td></tr>";
            echo "<tr><td>Full name</td>";
            echo "<td><input type=\"text\" id=\"fullName\" name=\"fullName\" autofocus=\"autofocus\" size=\"20\" /></td></tr>";
            echo "</table>";
            echo "<input type=\"submit\" id=\"add\" value=\"Add\"/>";
            echo "</form>";
            echo "</div>";
        }
    }
}
else {
    echo "Usage: ..setup.php?dbase=id_of_dbase";
}
include "../templates/footer.php";


function createTable($con)
{
    $table = new Table($con, 'users');
    $table->drop();

    $query = "CREATE TABLE `users` (
                `user` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `name` varchar(40) NOT NULL,
                `pass` varchar(255) NOT NULL,
                `email` varchar(40) NOT NULL,
                `lastlogin` timestamp NOT NULL DEFAULT current_timestamp(),
                `role` enum('admin','physician','assistent','secretary') NOT NULL,
                `fullName` varchar(80)
            )";
	$result = mysqli_query($con, $query);
}

/**
* addNewUser
*
*/
function addNewUser($con, $name, $fullName, $pass, $email, $role)
{
    $valid = false;
    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);// crypt($pass, $salt);
    $query = "INSERT INTO users (name, pass, email, role, fullName) VALUES ('" . $name . "','" . $hashedPass . "','" . $email . "','" . $role  . "','" . $fullName . "')";
    if ($result = mysqli_query($con, $query)) {
        $valid = true;
    }
    else {
        die('Could not add administrator: ' . mysqli_error($con));
	}
	return $valid;
}


?>
