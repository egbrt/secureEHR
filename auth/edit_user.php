<?php
require_once 'login.php';
startSecureSession();
?>

<html>
<head>
    <?php include "../templates/title.html" ?>
    <link type="text/css" rel="stylesheet" href="./auth.css" />
    <link type="text/css" rel="stylesheet" href="../styles/main.css" />
</head>
<body>

<?php
include "../templates/headAdmin.html";

if (isAuthenticated($con, $user, $name, $role))
{
    if ($role == "admin") {
        if (isset($_REQUEST['action'])) {
            if ($_REQUEST['action'] == "Change") {
                changeUser($con);
                echo "<p><a href=\"admin.php\">Back to list of users</a></p>";
            }
            elseif ($_REQUEST['action'] == "Delete") {
                deleteUser($con);
                echo "<p><a href=\"admin.php\">Back to list of users</a></p>";
            }
            /*
            elseif ($_REQUEST['action'] == "Reset") {
                resetUser($con);
                echo "<p><a href=\"admin.php\">Back to list of users</a></p>";
            }
            */
        }
        elseif (isset($_REQUEST['user'])) {
            editUser($con);
        }
    }
    else {
        echo "<p><a href=\"../login.php\">You're not an administrator</a></p>";
    }
}

include "../templates/footer.php";


function editUser($con)
{
    $user = $_REQUEST['user'];
    $query = "SELECT * FROM users WHERE user=" . $user;
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            echo "<div class=\"userList\">";
            echo "<form method=\"post\">";
            echo "<table>";
            echo "<tr><td>Role</td>";
            echo "<td><select id=\"userRole\" name=\"userRole\">";
            echo "<option value=\"admin\"";
            if ($row['role'] == "admin") echo " selected=\"selected\"";
            echo ">administrator</option>";
            echo "<option value=\"physician\"";
            if ($row['role'] == "physician") echo " selected=\"selected\"";
            echo ">physician</option>";
            echo "<option value=\"assistent\"";
            if ($row['role'] == "assistent") echo " selected=\"selected\"";
            echo ">physician assistent</option>";
            echo "<option value=\"secretary\"";
            if ($row['role'] == "secretary") echo " selected=\"selected\"";
            echo ">secretary</option>";
            echo "</select></td></tr>";
            
            echo "<tr><td>Name</td>";
            echo "<td>" . $row['name'] . "</td></tr>";
            echo "<tr><td>Email</td>";
            echo "<td><input type=\"email\" id=\"userEmail\" name=\"userEmail\" size=\"20\" value=\"" . $row['email'] . "\"/></td></tr>";
            echo "<tr><td>Password</td>";
            echo "<td><input type=\"password\" id=\"userPass\" name=\"userPass\" size=\"20\" /></td></tr>";
            echo "<tr><td>Full Name</td>";
            echo "<td><input type=\"text\" id=\"fullName\" name=\"fullName\" size=\"20\" value=\"". $row['fullName'] . "\"/></td></tr>";
            echo "</table>";
            echo "<input type=\"submit\" name=\"action\" value=\"Change\"/>";
            echo "<input type=\"submit\" name=\"action\" value=\"Delete\"/>";
            //echo "<input type=\"submit\" name=\"action\" value=\"Reset\"/>(Reset sends email to reset password)";
            echo "</form>";
            echo "</div>";
        }
        mysqli_free_result($result);
    }
}


function changeUser($con)
{
    $user = $_REQUEST['user'];
    $query = "SELECT * FROM users WHERE user=" . $user;
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            $pass = $row['pass'];
            if (isset($_POST['userPass'])) {
                $newPass = $_POST['userPass'];
                if ($newPass != "") {
                    $pass = password_hash($newPass, PASSWORD_DEFAULT);
                }
            }

            $email = $row['email'];
            if (isset($_POST['userEmail'])) {
                $newEmail = $_POST['userEmail'];
                if ($newEmail != "") {
                    $email = $newEmail;
                }
            }

            $role = $row['role'];	
            if (isset($_POST['userRole'])) {
                $newRole = $_POST['userRole'];
                if ($newRole != "") {
                    $role = $newRole;
                }
            }

            $name = $row['fullName'];
            if (isset($_POST['fullName'])) {
                $newName = $_POST['fullName'];
                if ($newName != "") {
                    $name = $newName;
                }
            }

            $query = "UPDATE users SET pass='" . $pass ."',email='" . $email . "',role='" . $role . "',fullName='" . $name . "' WHERE user=". $user;
            mysqli_query($con, $query);
        }
        mysqli_free_result($result);
    }
}


function deleteUser($con)
{
    $user = $_REQUEST['user'];
    $query = "DELETE FROM users WHERE user=" . $user;
    mysqli_query($con, $query);
}


/*
function resetUser($con)
{
    $user = $_REQUEST['user'];
    $query = "SELECT * FROM users WHERE user=" . $user;
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            $email = $row['email'];
            $name = $row['name'];
            $fullName = $row['fullName'];
            $salt = random_bytes(20);
            $pass = random_bytes(20);
            $hashedPass = crypt($pass, $salt);
            $query = "UPDATE users SET pass='" . $hashedPass . "', salt='" . $salt . "' WHERE user=" . $user;
            if ($result = mysqli_query($con, $query)) {
                $message = "Dear " . $fullName . ",\n\n";
                $message .= "you can reset your password by clicking the following link\n";
                $message .= "https://" . $_SERVER['SERVER_NAME'] . "/newuser.php?name=" . $name . "&fp=" . bin2hex($hashedPass) . "\n\n\n";
                $message .= "Kind Regards,\nEgbert van der Haring";
                $subject = "Reset password at " . $_SERVER['SERVER_NAME'];
                mail($email, $subject, $message);
                mysqli_free_result($result);
            }
        }
    }
}
*/

?>
