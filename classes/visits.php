<?php

if (!defined("PORTAL")) define("PORTAL", false);
if (PORTAL) {
    require_once "../classes/table.php";
    require_once "../classes/record.php";
    require_once "../classes/statements.php";
}
else {
    require_once "classes/table.php";
    require_once "classes/record.php";
    require_once "classes/statements.php";
}

class Visits extends Table {

    function __construct($con)
    {
        $this->con = $con;
        $this->name = "visits";
    }

    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `visits` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `episodeID` int(11) NOT NULL,
                    `date` date NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
            $query = "ALTER TABLE `visits`
                        ADD KEY `episodeID` (`episodeID`)";
            if (!mysqli_query($this->con, $query)) {
                echo "TABLE visits: " . mysqli_error($this->con);
                $valid = false;
            }
        }
        else {
            echo "TABLE visits: " . mysqli_error($con);
            $valid = false;
        }
        return $valid;
    }
    
    function get($episodeID)
    {
        $visits = false;
        $query = $this->con->prepare("SELECT * FROM visits WHERE episodeID = ?");
        $query->bind_param("i", $episodeID);
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $visits =$result->fetch_all(MYSQLI_ASSOC);
            }
        }
        return $visits;
    }
    
    function getIncludeStatements($episodeID)
    {
        $visits = array();
        $query = $this->con->prepare("SELECT visits.id, visits.date, skinds.name, statements.text FROM statements INNER JOIN skinds ON skinds.id = statements.kind INNER JOIN visits ON visits.id = statements.visitID WHERE visits.episodeID = ? ORDER BY visits.date DESC, visits.id DESC, statements.kind");
        $query->bind_param("i", $episodeID);
        $query->execute();
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $visits = $result->fetch_all(MYSQLI_ASSOC);
            }
        }
        $query->close();
        return $visits;
    }
}


class Visit extends Record {
    public $episodeId;
    public $date;
    public $diagnosis;
    public $intervention;
    
    function __construct($con, $id)
    {
        parent::__construct($con, "visits", $id);
        $this->episodeId = 0;
        $this->date = "";
        $this->diagnosis = "";
        $this->intervention = "";
        if ($row = $this->get()) {
            $this->id = $id;
            $this->episodeId = $row['episodeID'];
            $this->date = $row['date'];
        }
    }
    
    function import()
    {
        $valid = false;
        $query = $this->con->prepare("INSERT INTO visits (episodeID, date) VALUES(?, ?)");
        $query->bind_param("is", $this->episodeId, $this->date);
        if ($query->execute()) {
            $this->id = $query->insert_id;
            $valid = true;
        }
        $query->close();
        return $valid;
    }

    function save($episodeId, $date)
    {
        $this->date = $date;
        $this->episodeId = $episodeId;
        $this->import();
    }

    function addStatements($statements)
    {
        $i = 0;
        $episodeName = "";
        $drugs = new Drugs($this->con);
        $skinds = new SKinds($this->con);
        while ($i < count($statements)) {
            if ($statements[$i]['kind'] == "Assessment") {
                if ($episodeName != "") $episodeName .= " AND ";
                $episodeName .= $statements[$i]['label'];
                if ($this->diagnosis == "") $this->diagnosis = $statements[$i]['label'];
            }
            elseif ($statements[$i]['kind'] == "Medication") {
                if ($episodeName != "") {
                    $code = strtok($episodeName, " ");
                    $drugs->add($code, $statements[$i]['label']);
                }
            }
            elseif ($statements[$i]['kind'] == "Plan") {
                if ($this->intervention == "") $this->intervention = $statements[$i]['label'];
            }
            elseif ($statements[$i]['kind'] == "Objective") {
                if ($statements[$i]['label'][0] == "!") {
                    $statements[$i]['kind'] = "Measurement";
                    $statements[$i]['label'] = substr($statements[$i]['label'], 1);
                }
            }
            $statement = new Statement($this->con, 0);
            $statement->visitId = $this->id;
            $statement->kindId = $skinds->get($statements[$i]['kind']);
            $statement->text = $statements[$i]['label'];
            $statement->update();
            $i++;
        }
        return $episodeName;
    }
    
    function merge($visits)
    {
        $query = $this->con->prepare("UPDATE IGNORE statements SET visitID = ? WHERE visitID = ?");
        $query->bind_param("ii", $this->id, $visit);
        foreach ($visits as $visit) {
            $query->execute();
        }
        $query->close();

        // delete possibly orphaned statements
        $query = $this->con->prepare("DELETE FROM statements WHERE visitID = ?");
        $query->bind_param("i", $visit);
        foreach ($visits as $visit) {
            $query->execute();
        }
        $query->close();
        
        // delete merged visits
        $query = $this->con->prepare("DELETE FROM visits WHERE id = ?");
        $query->bind_param("i", $visit);
        foreach ($visits as $visit) {
            $query->execute();
        }
        $query->close();
    }
}
?>
