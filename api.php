<?php

header('Content-Type: application/json; charset=utf-8');

require_once "auth/login.php";
startSecureSession();
require_once "classes/patients.php";
require_once "classes/episodes.php";
require_once "classes/visits.php";
require_once "classes/statements.php";
require_once "classes/appointments.php";
require_once "classes/drugs.php";
require_once "classes/dis.php";
require_once "classes/bills.php";

class EpisodeVisits {
    public $episode;
    public $visits;
}


if ((isset($_REQUEST['operation'])) and isAuthenticated($con, $user, $name, $role)) {
    switch ($_REQUEST['operation']) {
        case "saveVisit":
            $episode = new Episode($con, $_REQUEST['episode']);
            saveVisit($con, $_REQUEST['patient'], $episode, $_REQUEST['date'], $_REQUEST['statements']);
            echo json_encode($episode);
            break;
        case "saveNotes":
            $notesId = $_REQUEST['notesId'];
            if ($notesId == 0) {// new notes
                $episode = new Episode($con, 0);
                saveVisit($con, $_REQUEST['patient'], $episode, date("Y-m-d"), $_REQUEST['statements']);
                $episode->setName("Notes");
                $statements = new Statements($con);
                echo json_encode($statements->getNotes($_REQUEST['patient']));
            }
            else { // update existing notes
                $statement = new Statement($con, $notesId);
                $notes = $_REQUEST['statements'];
                $statement->text = $notes[0]['label'];
                $statement->update();
                $statements = new Statements($con);
                echo json_encode($statements->getNotes($_REQUEST['patient']));
            }
            break;
        case "getNotes":
            $statements = new Statements($con);
            echo json_encode($statements->getNotes($_REQUEST['patient']));
            break;
        case "searchPatients":
            $crypto = new Crypto($_REQUEST['label']);
            if ($crypto->valid) {
                $patients = new Patients($con);
                $patient = $patients->search($crypto);
                if ($patient) {
                    $_SESSION["currentPatient"] = serialize($patient);
                }
                else {
                    unset($_SESSION["currentPatient"]);
                }
                echo json_encode($patient);
            }
            break;
        case "setPatientInfo":
            $crypto = new Crypto($_REQUEST['label']);
            if ($crypto->valid) {
                $patient = new Patient($con, $_REQUEST['patient']);
                $patient->saveChanges($crypto, $_REQUEST['email'], $_REQUEST['info']);
                $_SESSION["currentPatient"] = serialize($patient);
                echo json_encode($patient);
            }
            break;
        case "getPatientEpisodes":
            $episodes = new Episodes($con);
            echo json_encode($episodes->get($_REQUEST['patient']));
            break;
        case "toggleEpisodeActive":
            $episode = new Episode($con, $_REQUEST["episode"]);
            $episode->active = !$episode->active;
            $episode->update();
            echo json_encode($episode);
            break;
        case "getPatientLastVisits":
            $statements = new Statements($con);
            echo json_encode($statements->getPatientStatements($_REQUEST['patient'], array("Reason")));
            break;
        case "getPatientDrugs":
            $statements = new Statements($con);
            echo json_encode($statements->getPatientStatements($_REQUEST['patient'], array("Medication")));
            break;
        case "getPatientLaboratory":
            $statements = new Statements($con);
            echo json_encode($statements->getPatientStatements($_REQUEST['patient'], array("Laboratory", "Measurement")));
            break;
        case "getPatientFunctioning":
            $statements = new Statements($con);
            echo json_encode($statements->getPatientStatements($_REQUEST['patient'], array("Reason", "Assessment", "Objective", "Subjective"), array("2F%", "2R%")));
            break;
        case "getDrugs":
            $drugs = new Drugs($con);
            echo json_encode($drugs->find($_REQUEST['icpc']));
            break;
        case "getVisits":
            $visits = new Visits($con);
            $episodeVisits = new EpisodeVisits();
            $episodeVisits->episode = new Episode($con, $_REQUEST["episode"]);
            $episodeVisits->visits = $visits->getIncludeStatements($_REQUEST['episode']);
            echo json_encode($episodeVisits);
            break;
        case "mergeEpisodes":
            $main = new Episode($con, $_REQUEST['main']);
            $sub = new Episode($con, $_REQUEST['sub']);
            $main->merge($sub);
            echo json_encode($main);
            break;
        case "moveVisits":
            $episode = new Episode($con, $_REQUEST['episode']);
            if ($episode->id == 0) {
                $episode->save($_REQUEST['patient'], date('Y-m-d'));
            }
            $episode->mergeVisits($_REQUEST['visits']);
            echo json_encode($episode);
            break;
        case "mergeVisits":
            $visit = new Visit($con, $_REQUEST['visit']);
            $visit->merge($_REQUEST['visits']);
            echo json_encode($visit);
            break;
        case "getAppointments":
            $appointments = new Appointments($con);
            echo json_encode($appointments->get($_REQUEST['date']));
            break;
        case "addAppointment":
            $appointments = new Appointments($con);
            $appointments->add($_REQUEST['patient'], $_REQUEST['date'], $_REQUEST['starttime'], $_REQUEST['endtime']);
            echo json_encode($appointments->get($_REQUEST['date']));
            break;
        case "deleteAppointment":
            $appointments = new Appointments($con);
            $appointments->delete($_REQUEST['appointment']);
            echo json_encode($appointments->get($_REQUEST['date']));
            break;
        case "getDisInterventions":
            $dis = new Dis($con);
            echo json_encode($dis->getInterventions($_REQUEST['diagnosis']));
            break;
        case "setImportance":
            $statement = new Statement($con, $_REQUEST["statement"]);
            $statement->importance = $_REQUEST["importance"];
            $statement->update();
            echo json_encode($statement);
            break;
    }
    mysqli_close($con);
}


function saveVisit($con, $patientId, $episode, $date, $statements)
{
    if ($episode->id == 0) {
        $episode->save($patientId, $date);
    }
    $visit = new Visit($con, 0);
    $visit->save($episode->id, $date);
    $episode->addVisit($visit);
    
    $episodeName = $visit->addStatements($statements);
    if ($episodeName != "") $episode->setName($episodeName);
    
    if (($visit->diagnosis != "") and ($visit->intervention != "")) {
        $dis = new Dis($con);
        if ($billing = $dis->getBilling(strtok($visit->diagnosis, " "), strtok($visit->intervention, " "))) {
            $bills = new Bills($con);
            $bills->add($patientId, $date, $visit->diagnosis, $visit->intervention, $billing);
        }
    }
}

?>
