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

if (isAuthenticated($con, $user, $name, $role)) {
    if ($role == "admin") {
        if (getCredentials($userName, $pass, $email, $role, $fullName)) {
            addNewUser($con, $userName, $pass, $email, $role, $fullName);
        }
        showUsers($con);
    }
    else {
        echo "You're not an administrator";
        $_SESSION = array();
        showPasswordForm();
    }
}
else {
    showPasswordForm();
}
include "../templates/footer.php";


/**
* showUsers
* shows users
*/
function showUsers($con)
{
    $query = "SELECT * FROM users";
    if ($result = mysqli_query($con, $query)) {
        echo "<div class=\"userList\">";
        echo "<table border=\"1\">";
        echo "<tr><td>user</td><td>name</td><td>email</td><td>last login</td><td>role</td></tr>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<tr>";
            echo "<td>" . $row['user'] . "</td>";
            echo "<td><a href=\"edit_user.php?user=" . $row['user'] . "\">" . $row['name'] . "</td>";
            //echo "<td>" . $row['pass'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['lastlogin'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        mysqli_free_result($result);
    }
    echo "<div class=\"newUser\">";
    echo "<form method=\"post\">";
    echo "<table><tr><th colspan=2>Add new user</th></tr>";

    echo "<tr><td>Role</td>";
    echo "<td><select id=\"userRole\" name=\"userRole\">";
    echo "<option value=\"admin\">administrator</option>";
    echo "<option value=\"physician\">physician</option>";
    echo "<option value=\"assistent\">physician assistent</option>";
    echo "<option value=\"secretary\">secretary</option>";
    echo "</select></td></tr>";

    echo "<tr><td>Name</td>";
    echo "<td><input type=\"text\" id=\"userName\" name=\"userName\" autofocus=\"autofocus\" size=\"20\" /></td></tr>";
    echo "<tr><td>Email</td>";
    echo "<td><input type=\"email\" id=\"userEmail\" name=\"userEmail\" size=\"20\" /></td></tr>";
    echo "<tr><td>Password</td>";
    echo "<td><input type=\"password\" id=\"userPass\" name=\"userPass\" size=\"20\" /></td></tr>";
    echo "<tr><td>Full Name</td>";
    echo "<td><input type=\"text\" id=\"fullName\" name=\"fullName\" size=\"20\" /></td></tr>";
    echo "</table>";
    echo "<input type=\"submit\" id=\"add\" value=\"Add\"/>";
    echo "</form>";
    echo "</div>";
}


/**
* addNewUser
*
*/
function addNewUser($con, $name, $pass, $email, $role, $fullName)
{
    global $EMAIL_FROM, $EMAIL_GREETINGS, $EMAIL_SUBJECT;
    $query = "SELECT * FROM users WHERE name='" . $name . "'";
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            echo "user exists";
        }
        else {
            $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, pass, email, role, fullName) VALUES ('" . $name . "','" . $hashedPass . "','" . $email . "','" . $role . "','" . $fullName . "')";
            if ($result = mysqli_query($con, $query)) {
                $headers = "From: " . $EMAIL_FROM;

                $message = "Dear " . $fullName . ",\n\n";
                $message .= "you can login to your new account by clicking the following link\n";
                $message .= "https://" . $_SERVER['SERVER_NAME'] . "/newuser.php?dbase=" . $_SESSION['dbase'] . "&name=" . $name . "&fp=" . bin2hex($hashedPass) . "\n\n\n";
                $message .= $EMAIL_GREETINGS;
                $subject = $EMAIL_SUBJECT;
                if (!mail($email, $subject, $message, $headers)) {
                    echo "Failed to send email<br/>";
                    echo $headers . "<br/>Subject: " . $subject . "<br/>" . $message . "<br/>";
                }
            }
        }
    }
}
?>
