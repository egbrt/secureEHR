<?php
require_once "dbase.php";


/**
* startSecureSession
* starts a secure session, must be called at top of page where session is needed
*/
function startSecureSession()
{
    if (defined("PORTAL")) {
        $session_name = 'emr_portal_session_id'; // Set a custom session name
    }
    else {
        $session_name = 'emr_session_id'; // Set a custom session name
    }
    $secure = true;  // Set to true if using https.
    $httponly = true; // This stops javascript being able to access the session id. 
 
    ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies. 
    $cookieParams = session_get_cookie_params(); // Gets current cookies params.
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly); 
    session_name($session_name); // Sets the session name to the one set above.
    session_start(); // Start the php session
    //session_regenerate_id(true); // regenerated the session, delete the old one.  
}


/**
* showPasswordForm
* shows password form
*/
function showPasswordForm()
{
    echo "<div class=\"loginForm\">";
    echo "<form method=\"post\">";
    echo "<table>";
    echo "<tr><td>Database</td>";
    echo "<td><select id=\"userDB\" name=\"userDB\">";
    foreach (DBASES as $id => $params) {
        echo "<option value=\"" . $id . "\"";
        echo ">" . $id . "</option>";
    }
    echo "</td></tr>";
    echo "<tr><td>Name</td>";
    echo "<td><input type=\"text\" id=\"userName\" name=\"userName\" autofocus=\"autofocus\" size=\"20\" /></td></tr>";
    echo "<tr><td>Password</td>";
    echo "<td><input type=\"password\" id=\"userPass\" name=\"userPass\" size=\"20\" /></td></tr>";
    echo "</table>";
    echo "<input type=\"submit\" id=\"login\" value=\"Login\"/>";
    echo "</form>";
    /*
    echo "<hr><p>Don't remember your password?<br/> Then click the link below:</p>";
    $server = $_SERVER['SERVER_NAME'];
    if ($server == "localhost") $server .= "/claw";
    echo "<p><a href=\"https://" . $server . "/resetPassword.php\">Reset password</a></p>";
    */
    echo "</div>";
}


/**
* isAuthenticated
* returns true when user is authenticated
*/
function isAuthenticated(&$con, &$user, &$name, &$role)
{
    $valid = false;
    if (isset($_SESSION['authenticated'])) {
        if (isset($_SESSION['dbase']) and connectToDatabase($_SESSION['dbase'], $con)) {
            if (isset($_SESSION['user'])) $user = $_SESSION['user'];
            if (isset($_SESSION['role'])) $role = $_SESSION['role'];
            if (isset($_SESSION['name'])) $name = $_SESSION['name'];
            $valid = true;
        }
    }
    elseif (isset($_POST['userDB']) and connectToDatabase($_POST['userDB'], $con)) {
        $_SESSION['dbase'] = $_POST['userDB'];
        if (isset($_POST['userName']) and isset($_POST['userPass'])) {
            $userName = $_POST['userName'];
            $userPass = $_POST['userPass'];
            $valid = isAuthenticatedUser($con, $userName, $userPass, $user, $name, $role);
        }            
    }
    else {
        //echo "not authenticated<br/>";
    }
    return $valid;
}


function isAuthenticatedUser($con, $userName, $userPass, &$user, &$name, &$role)
{
    $valid = false;
    $query = "SELECT * FROM users WHERE name='" . $userName . "'";
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            if (password_verify($userPass, $row['pass'])) {
                $_SESSION['authenticated'] = true;
                $_SESSION['user'] = $row['user'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                $role = $row['role'];
                $user = $row['user'];
                $name = $row['name'];
                $valid = true;
            }
        }
        mysqli_free_result($result);
    }
    return $valid;
}


function setLastLogin($con, $user)
{
	$query = "UPDATE users SET lastlogin=\"" . date("Y-m-d H:i:s") . "\" WHERE user=" . $user;
	//echo $query;
	mysqli_query($con, $query);
}


/**
* getCredentials
*
*/
function getCredentials(&$name, &$pass, &$email, &$role, &$fullName)
{
	$valid = false;
	if (isset($_POST['userName'])) {
		$name = $_POST['userName'];
		if (($name != "") && (isset($_POST['userPass']))) {
			$pass = $_POST['userPass'];
			if (($pass != "") && (isset($_POST['userEmail']))) {
				$email = $_POST['userEmail'];
				if (($email != "") && (isset($_POST['userRole']))) {
					$role = $_POST['userRole'];
					$valid = ($role != "");
					$fullName = "";
					if ($valid) {
                        if (isset($_POST['fullName'])) $fullName = $_POST['fullName'];
                    }
				}
			}
		}
	}
	return $valid;
}


?>
