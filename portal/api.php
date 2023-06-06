<?php

define("PORTAL", true);
require_once "../auth/login.php";
startSecureSession();
require_once "../classes/patients.php";
require_once "../classes/episodes.php";
require_once "../classes/statements.php";

class Info {
    public $patient;
    public $diagnoses;
    public $medication;
    public $laboratory;
    public $functioning;
}

if (isset($_REQUEST['reset'])) {
    header('Content-Type: application/json; charset=utf-8');
    unset($_SESSION['authenticated']);
    echo json_encode("Ok");
}
elseif ((isset($_SESSION['authenticated'])) /*and (isset($_SESSION['patient']))*/ and (connectToDatabase("praxis1", $con))) {
    header('Content-Type: application/json; charset=utf-8');
    $info = new Info();
    $info->patient = unserialize($_SESSION["patient"]);
    
    $episodes = new Episodes($con);
    $info->diagnoses = $episodes->get($info->patient->id);

    $statements = new Statements($con);
    $info->medication = $statements->getPatientStatements($info->patient->id, array("Medication"));
    $info->laboratory = $statements->getPatientStatements($info->patient->id, array("Laboratory", "Measurement"));
    $info->functioning = $statements->getPatientStatements($info->patient->id, array("Reason", "Assessment", "Objective", "Subjective"), array("2F%", "2R%"));
    echo json_encode($info);
}
?>
