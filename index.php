<?php

include "templates/header.php";
require_once "templates/menu.php";
require_once "auth/user.php";
require_once "classes/database.php";

if (isAuthenticated($con, $user, $name, $role)) {
    setLastLogin($con, $user);
    showMenu($user, $role);
    $page = "patients";
    if (isset($_REQUEST['page'])) $page = $_REQUEST['page'];
    switch ($page) {
        case "patients":
            include "templates/patients.php";
            break;
        case "calendar":
            include "templates/calendar.php";
            break;
        case "reports":
            $patients = new Patients($con);
            include "templates/reports.php";
            break;
        case "billing":
            $bills = new Bills($con);
            include "templates/billing.php";
            break;
        case "dxContacts":
            $statements = new Statements($con);
            include "templates/dxContacts.php";
            break;
        case "dxEpisodes":
            $episodes = new Episodes($con);
            include "templates/dxEpisodes.php";
            break;
        case "dashboard":
            if (isset($_REQUEST["patient"]) and isset($_SESSION["currentPatient"])) {
                $patient = unserialize($_SESSION["currentPatient"]);
                if ($_REQUEST["patient"] == $patient->id) {
                    include "templates/dashboard.php";
                }
            }
            break;
    }
    mysqli_close($con);
}
else {
    showPasswordForm();
    include "templates/intro.php";
}
include "templates/footer.php";

?>

