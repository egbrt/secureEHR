<?php

define("PORTAL", true);
require_once "../auth/login.php";
startSecureSession();
require_once "../classes/patients.php";
require_once "../classes/codegenerator.php";

if ((isset($_REQUEST["identification"])) and ($_REQUEST["identification"] != "")) {
    include "templates/header.html";
    if (connectToDatabase("praxis1", $con)) {
        if (sendCode($con, $_REQUEST["identification"])) {
            include "templates/login_step_2.html";
            if ($_SERVER['SERVER_NAME'] == 'localhost') {
                echo "<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>sixcode= " . $_SESSION["sixcode"] . "</p>";
                echo "<p>patient= " . $_SESSION["patient"] . "</p>";
            }
        }
        else {
            include "templates/login_failed.html";
        }
    }
    else {
        echo "<p>Failed to connect to database.</p>";
    }
    include "templates/footer.html";
}
elseif (isset($_REQUEST["sixcode"])) {
    if ($_REQUEST["sixcode"] == $_SESSION["sixcode"]) {
        $_SESSION['authenticated'] = true;
        $server = $_SERVER['SERVER_NAME'];
        if ($server == 'localhost') {
            header('Location: ../portal/index.html');
        }
        else {
            header('Location: index.html');
        }
    }
    else {
        include "templates/header.html";
        include "templates/login_step_1.html";
        include "templates/footer.html";
    }
}
else {
    include "templates/header.html";
    include "templates/login_step_1.html";
    include "templates/footer.html";
}


function sendCode($con, $identification)
{
    global $EMAIL_FROM;

    $valid = false;
    $crypto = new Crypto($identification);
    if ($crypto->valid) {
        $patients = new Patients($con);
        $patient = $patients->search($crypto);
        if ($patient) {
            $valid = true;
            $cg = new CodeGenerator(6);
            $_SESSION["sixcode"] = $cg->code;
            $_SESSION["patient"] = serialize($patient);
            
            $headers = "From: " . $EMAIL_FROM;
            $message = "Dear " . $patient->label . ",\n\n";
            $message .= "your authorization code is: " . $cg->code . "\n\n";
            $message .= "Greetings,\nICPC-3 secureEHR Portal\n";
            $subject = "ICPC-3 secureEHR Portal";
            if (!mail($patient->email, $subject, $message, $headers)) {
                echo "Failed to send email<br/>";
                echo $headers . "<br/>Subject: " . $subject . "<br/>" . $message . "<br/>";
            }
        }
    }
    return $valid;
}

?>

