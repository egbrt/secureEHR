<?php

include "templates/header.php";

if (isset($_REQUEST['action']) and ($_REQUEST['action'] == "Submit")) {
    if (connectToDatabase($_SESSION['dbase'], $con)) {
        if (changeSettings($con)) {
            jumpToLogin($con);
        }
    }
}
elseif (isset($_REQUEST['dbase']) and isset($_REQUEST['name']) and isset($_REQUEST['fp'])) {
    if (connectToDatabase($_REQUEST['dbase'], $con)) {
        $_SESSION['dbase'] = $_REQUEST['dbase'];
        if (addNewUser($con, $exists)) {
            showPrivacyStatement();
        }
        elseif ($exists) {
            jumpToLogin($con);
        }
    }
}


function addNewUser($con, &$exists)
{
    $valid = false;
    $exists = false;
    $name = $_REQUEST['name'];
    $fp = $_REQUEST['fp'];
    $hashedPass = hex2bin($fp);
    $query = "SELECT * FROM users WHERE name='" . $name . "'";
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            $exists = true;
            $_SESSION['name'] = $name;
            $_SESSION['user'] = $row['user'];
            if ($hashedPass == $row['pass']) {
                $valid = true;
                echo "<div id=\"newUser\">";
                echo "<form method=\"post\">";
                echo "<table>";
                echo "<tr><td>Account</td>";
                echo "<td>" . $row['name'] . "</td></tr>";
                echo "<tr><td>Name</td>";
                echo "<td><input type=\"text\" id=\"fullName\" name=\"fullName\" size=\"20\" value=\"". $row['fullName'] . "\"/></td></tr>";
                echo "<tr><td>Email</td>";
                echo "<td><input type=\"email\" id=\"userEmail\" name=\"userEmail\" size=\"20\" value=\"" . $row['email'] . "\"/></td></tr>";
                echo "<tr><td>Password</td>";
                echo "<td><input type=\"password\" id=\"userPass\" name=\"userPass\" size=\"20\" /></td></tr>";
                echo "<tr><td>Repeat</td>";
                echo "<td><input type=\"password\" id=\"userPass2\" name=\"userPass2\" size=\"20\" onkeyup=\"checkEmpty()\" /></td></tr>";
                echo "<tr><td></td><td id=\"pwEqual\" style=\"visibility:hidden\">Repeat not equal to Password!</td></tr>";
                echo "</table>";
                echo "<p><input type=\"checkbox\" id=\"okPS\" onclick=\"checkEmpty()\">I've read the ";
                echo "<span onclick=\"showPS()\" style=\"text-decoration:underline; cursor:pointer\">privacy statement</span>.</p>";
                echo "<input name=\"action\" type=\"submit\" id=\"change\" disabled=\"true\" value=\"Submit\"/>";
                echo "</form>";
                echo "</div>";
                echo "<script>";
                echo "function showPS() {";
                echo "ps = document.getElementById(\"privacyStatement\");";
                echo "ps.style.visibility = \"visible\";";
                echo "}";
                echo "function checkEmpty() {";
                echo "pw=document.getElementById(\"userPass\").value;";
                echo "pw2=document.getElementById(\"userPass2\").value;";
                echo "ps=document.getElementById(\"okPS\").checked;";
                echo "button=document.getElementById(\"change\");";
                echo "pwEq=document.getElementById(\"pwEqual\");";
                echo "if (pw == pw2) {pwEq.style.visibility = \"hidden\";} else {pwEq.style.visibility = \"visible\"}";
                echo "button.disabled=((ps==false) || (pw==\"\") || (pw!=pw2));";
                echo "}";
                echo "</script>";
            }
        }
    }
    return $valid;
}


function changeSettings($con)
{
    $valid = false;
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
        $name = $_SESSION['name'];
        $hashedPass = password_hash($_POST['userPass'], PASSWORD_DEFAULT);
        $email = $_POST['userEmail'];
        $fullName = $_POST['fullName'];
        $query = "UPDATE users SET pass='" . $hashedPass ."',email='" . $email . "',fullName='" . $fullName . "' WHERE user=". $user;
        if ($result = mysqli_query($con, $query)) {
            $valid = true;
            echo "<p>Dear " . $fullName . ",</p><p>your changes have been saved.</p>";
            echo "<p>Please click the link below and login with your account name (" . $name . ") and your new password.</p>";
        }
        else {
            echo "<p>Could not save changes, please contact us.</p>";
            echo mysqli_error($con);
        }
    }
    return $valid;
}


function jumpToLogin($con)
{
    global $APP_LOCATION;
    
    $user = $_SESSION['user'];
    $query = "SELECT role FROM users WHERE user=" . $user;
    if ($result = mysqli_query($con, $query)) {
        if ($row = mysqli_fetch_array($result)) {
            if ($row['role'] == 'viewer') {
                echo "<p><a href=\"" . $APP_LOCATION . "\">Login to comment system.</a></p>";
                echo "<div id=\"newUserInfo\">";
                echo "<p>In the unlikely event, that you can't login, send us an email with the following information:</p><ul>";
                echo "<li>the device you are using:<ul><li>desktop computer</li><li>tablet</li><li>phone</li></ul></li>";
                echo "<li>which operating system you are running on the device:<ul><li>windows</li><li>ios</li><li>android</li></ul></li>";
                echo "<li>what did you do?</li>";
                echo "<li>and what happened?</li>";
                echo "</ul></div>";
            }
            else {
                echo "<p><a href=\"index.php\">Go to login</a></p>";
            }
        }
    }
}


function showPrivacyStatement()
{
    echo "<div id=\"newUserInfo\">";
    echo "<h1>Important Information</h1>";
    echo "<p>Please, remember your account name (<b>" . $_REQUEST['name'] . "</b>) and the <b>password</b> you entered above. ";
    echo "These are needed to login to the ICPC secureEHR.</p>";
    echo "<p><b>NOTA BENE:</b> Do not use an <b>&</b> in your password, because that may cause problems.</p>";
    echo "</div>";

    echo "<div id=\"privacyStatement\" onclick=\"hidePS()\">";
    echo "<h1>Privacy Policy Statement</h1>";
    echo "<p>Welcome to the ICPC secureEHR.</p>";
    echo "<p>We recognize that your privacy is very important and take it seriously.</p>";
    echo "<p>This Privacy Policy describes our policies and procedures on the collection, use and disclosure of your information when you use the services, websites, and applications offered by us. (collectively, the “Services”)</p>";
    echo "<p>By using the Services, you consent to our use of your information in accordance with this Privacy Policy.</p>";
    echo "<p>We will not use or share your personal information except as described in this Privacy Policy.</p>";
    echo "<h2>Information We Collect and How We Use It</h2>";
    echo "<p>The database collects personal information to provide you with a personalized, useful and efficient experience. However, in each instance we collect only the minimum personally identifiable information necessary for the ICPC secureEHR. The categories of information we collect can include:</p>";
    echo "<h3>Information You Directly Provide to Us</h3>";
    echo "<p>The occasions when you provide information that may enable us to identify you personally (“Personally Identifiable Information”) while using the Services, such as when you sign up for an account, verify your identity or provide account information.</p>";
    echo "<p>Personally Identifiable Information we collect may include, without limitation, your email address and name.</p>";
    echo "<p>We may use your email address to send you updates about the ICPC secureEHR.</p>";
    echo "<h2>How We Use Your Information</h2>";
    echo "<p>In addition to some of the specific uses of information we describe in this Privacy Policy, we may use information that we receive for the following: to deliver and improve ICPC secureEHR; to manage your account and provide support; to communicate with you; to diagnose or fix technological problems; to verify your identity and prevent fraud or other unauthorized or illegal activity; to enforce or exercise any rights in our Terms of Service; and to perform functions or services as otherwise described to you at the time of collection.</p>";
    echo "<p>We will retain your information for as long as your account is active or is needed to provide you the Services and may retain certain Data after you discontinue use of the Services to comply with our legal obligations, to resolve disputes, and to enforce our agreements.</p>";
    echo "<h2>How We Share Your Information</h2>";
    echo "<p>We will not share your personal information with third parties.</p>";
    echo "<h2>How to Contact Us</h2>";
    echo "<p>If you have any questions about our practices or this Privacy Policy, please contact us at info@eggbird.eu.</p>";
    echo "</div>";

    echo "<script>";
    echo "function hidePS() {";
    echo "ps = document.getElementById(\"privacyStatement\");";
    echo "ps.style.visibility = \"hidden\";";
    echo "}";
    echo "</script>";
}


?>


