<?php

include "templates/header.php";
require_once "templates/menu.php";
require_once "auth/user.php";
require_once "classes/database.php";
require_once "classes/downloader.php";
require_once "classes/uploader.php";

if (isAuthenticated($con, $user, $name, $role) and ($role == "admin")) {
    showMenu($user, $role);
    if (isset($_REQUEST['submit'])) {
        $action = $_REQUEST['submit'];
        if ($action == "Delete") {
            if (isset($_SESSION["currentPatient"])) {
                $patient = unserialize($_SESSION["currentPatient"]);
                // we have to create a new patient because $con is not saved in the session object
                $patient = new Patient($con, $patient->id);
                deleteEpisodes($con, $patient->id);
                $patient->delete();
                unset($_SESSION["currentPatient"]);
                //header('Location: ./index.php');
            }
        }
        elseif ($action == "Export") {
            if (isset($_SESSION["currentPatient"])) {
                $patient = unserialize($_SESSION["currentPatient"]);
                $downloader = new Downloader($con, $patient->id);
                $downloader->write();
                
                echo "<div id='exportResults'>";
                if (file_exists($downloader->filename)) {
                    echo "<p>Click the file name to download:<br/>";
                    echo "<a href=\"" . $downloader->filename ."\" download>" . basename($downloader->filename) . "</a></p>";
                }
                else {
                    echo "<p>There is no file created.</p>";
                }
                echo "</div>";
            }
        }
        elseif ($action == "Import") {
            $uploader = new Uploader("xml");
            if ($uploader->uploadFile()) {
                if ($uploader->parseXML($con)) {
                    header('Location: ./index.php');
                }
                else {
                    showFeedback($uploader->errors);
                }
            }
        }
    }
    else {
        include "templates/management.php";
    }
} else {
    header('Location: ./index.php');
}
include "templates/footer.php";


function showFeedback($errors)
{
    echo "The file could not be (completely) parsed, because it contains the following errors:<br/>";
    echo $errors;
}

function deleteEpisodes($con, $patientId)
{
    $episodes = new Episodes($con);
    if ($e = $episodes->get($patientId)) {
        foreach ($e as $row) {
            deleteVisits($con, $row["id"]);
            $episode = new Episode($con, $row["id"]);
            $episode->delete();
        }
    }
}

function deleteVisits($con, $episodeId)
{
    $visits = new Visits($con);
    if ($v = $visits->get($episodeId)) {
        foreach ($v as $row) {
            deleteStatements($con, $row["id"]);
            $visit = new Visit($con, $row["id"]);
            $visit->delete();
        }
    }
}

function deleteStatements($con, $visitId)
{
    $statements = new statements($con);
    if ($s = $statements->getPure($visitId)) {
        foreach ($s as $row) {
            $statement = new Statement($con, $row["id"]);
            $statement->delete();
        }
    }
}
?>
