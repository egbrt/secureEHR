<?php

if (!defined("PORTAL")) define("PORTAL", false);
if (PORTAL) {
    require_once "../classes/table.php";
    require_once "../classes/record.php";
    require_once "../classes/visits.php";
}
else {
    require_once "classes/table.php";
    require_once "classes/record.php";
    require_once "classes/visits.php";
}

const DEFAULT_EPISODE_NAME = "Episode without Assessment";


class Episodes extends Table {

    function __construct($con)
    {
        $this->con = $con;
        $this->name = "episodes";
    }

    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `episodes` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `patientID` int(11) NOT NULL,
                    `active` tinyint(1) NOT NULL,
                    `startdate` date NOT NULL,
                    `enddate` date NOT NULL,
                    `name` varchar(400) NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
            $query = "ALTER TABLE `episodes`
                        ADD KEY `patientID` (`patientID`)";
            if (!mysqli_query($this->con, $query)) {
                echo "TABLE episodes: " . mysqli_error($this->con);
                $valid = false;
            }
        }
        else {
            echo "TABLE episodes: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }

    function get($patientID)
    {
        $episodes = array();
        $query = $this->con->prepare("SELECT * FROM episodes WHERE patientID = ? ORDER BY enddate DESC, id DESC");
        $query->bind_param("i", $patientID);
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $episodes = $result->fetch_all(MYSQLI_ASSOC);
            }
        }
        $query->close();
        return $episodes;
    }
    
    
    function getAll()
    {
        $rows = false;
        if ($result = $this->con->query("SELECT * FROM episodes ORDER BY name")) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $result->free_result();
        }
        return $rows;
    }

}


class Episode extends Record {
    public $active;
    public $patientId;
    public $startdate;
    public $enddate;
    public $name;
    
    function __construct($con, $id)
    {
        parent::__construct($con, "episodes", $id);
        $this->active = false;
        $this->patientId = 0;
        $this->startdate = "";
        $this->enddate = "";
        $this->name = "";
        if ($row = $this->get()) {
            $this->active = $row["active"];
            $this->patientId = $row['patientID'];
            $this->startdate = $row['startdate'];
            $this->enddate = $row['enddate'];
            $this->name = $row['name'];
        }
    }
    
    function import()
    {
        $valid = false;
        $query = $this->con->prepare("INSERT INTO episodes (active, patientID, startdate, enddate, name) VALUES(?, ?, ?, ?, ?)");
        $query->bind_param("iisss", $this->active, $this->patientId, $this->startdate, $this->enddate, $this->name);
        if ($query->execute()) {
            $this->id = $query->insert_id;
            $valid = true;
        }
        $query->close();
        return $valid;
    }
    
    function save($patientId, $date)
    {
        $this->patientId = $patientId;
        $this->startdate = $date;
        $this->enddate = $date;
        $this->name = DEFAULT_EPISODE_NAME;
        $this->active = true;
        $this->import();
    }
    
    function update()
    {
        $query = $this->con->prepare("UPDATE episodes SET active = ?, name = ?, startdate = ?, enddate = ? WHERE id = ?");
        $query->bind_param("isssi", $this->active, $this->name, $this->startdate, $this->enddate, $this->id);
        $query->execute();
        $query->close();
    }

    function setName($name)
    {
        if ($this->name != $name) {
            $this->name = $name;
            $query = $this->con->prepare("UPDATE episodes SET name = ? WHERE id = ?");
            $query->bind_param("si", $name, $this->id);
            $query->execute();
            $query->close();
        }
    }
    
    function updateName()
    {
        $name = "";
        $startdate = "";
        $enddate = "";
        $delete = false;
        $firstAssessment = true;
        $skinds = new SKinds($this->con);
        $assessment = $skinds->get("Assessment");
        $query = $this->con->prepare("SELECT statements.kind, statements.text, visits.date FROM statements INNER JOIN visits ON visits.id=statements.visitID WHERE visits.episodeID = ? ORDER BY visits.date DESC, statements.id DESC");
        $query->bind_param("i", $this->id);
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $i = 0;
                $delete = true;
                while ($row = $result->fetch_assoc()) {
                    $delete = false;
                    if (($firstAssessment) and ($row['kind'] == $assessment)) {
                        if ($name != "") $name .= " AND ";
                        $name .= $row['text'];
                    }
                    elseif ($name != "") {
                        $firstAssessment = false;
                    }
                    if (($startdate == "") or ($startdate > $row['date'])) {
                        $startdate = $row['date'];
                    }
                    if (($enddate == "") or ($enddate < $row['date'])){
                        $enddate = $row['date'];
                    }
                    $i++;
                }
            }
        }
    
        if ($delete) {
            $this->delete();
        }
        else {
            if ($name == "") $name = DEFAULT_EPISODE_NAME;
            $this->name = $name;
            $this->startdate = $startdate;
            $this->enddate = $enddate;
            $this->update();
        }
    }

    function addVisit($visit)
    {
        $update = false;
        if ($visit->date < $this->startdate) {
            $this->startdate = $visit->date;
            $update = true;
        }
        if ($visit->date > $this->enddate) {
            $this->enddate = $visit->date;
            $update = true;
        }
        if ($update) {
            $query = $this->con->prepare("UPDATE episodes SET startdate = ?, enddate= ? WHERE id = ?");
            $query->bind_param("ssi", $this->startdate, $this->enddate, $this->id);
            $query->execute();
            $query->close();
        }
    }

    function merge($sub)
    {
        $query = $this->con->prepare("UPDATE visits SET episodeID = ? WHERE episodeID = ?");
        $query->bind_param("ii", $this->id, $sub->id);
        $valid = $query->execute();
        $query->close();
        if ($valid) {
            $this->updateName();
            $sub->delete();
        }
    }

    function mergeVisits($visits)
    {
        $episodeSrc = null;
        $query = $this->con->prepare("SELECT * FROM visits WHERE id = ?");
        $query->bind_param("i", $visit);
        $visit = $visits[0];
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                if ($row = $result->fetch_assoc()) {
                    $episodeSrc = new Episode($this->con, $row['episodeID']);
                }
            }
        }
        $query->close();
    
        $query = $this->con->prepare("UPDATE visits SET episodeID = ? WHERE id = ?");
        $query->bind_param("ii", $this->id, $visit);
        foreach ($visits as $visit) {
            $query->execute();
        }
        $query->close();
        
        $this->updateName();
        if ($episodeSrc != null) {
            $episodeSrc->updateName();
        }
    }
}

?>
