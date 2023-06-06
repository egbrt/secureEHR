<?php

if (!defined("PORTAL")) define("PORTAL", false);
if (PORTAL) {
    require_once "../classes/table.php";
    require_once "../classes/record.php";
    require_once "../classes/skinds.php";
}
else {
    require_once "classes/table.php";
    require_once "classes/record.php";
    require_once "classes/skinds.php";
}


class Statements extends Table {
    private $skinds;

    function __construct($con)
    {
        $this->con = $con;
        $this->name = "statements";
        $this->skinds = new SKinds($con);
    }

    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `statements` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `visitID` int(11) NOT NULL,
                    `importance` smallint(6) NOT NULL DEFAULT 0,
                    `kind` smallint(6) NOT NULL DEFAULT 0,
                    `text` text NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
            $query = "ALTER TABLE `statements` ADD KEY `visitID` (`visitID`)";
            if (!mysqli_query($this->con, $query)) {
                echo "TABLE statements: " . mysqli_error($this->con);
                $valid = false;
            }
            $query = "ALTER TABLE `statements` ADD UNIQUE( `visitID`, `kind`, `text`(40))";
            if (!mysqli_query($this->con, $query)) {
                echo "TABLE statements: " . mysqli_error($this->con);
                $valid = false;
            }
        }
        else {
            echo "TABLE statements: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }

    function getPure($visitID)
    {
        $query = $this->con->prepare("SELECT * FROM statements WHERE visitID = ?");
        $query->bind_param("i", $visitID);
        $query->execute();
        $result = $query->get_result();
        $query->close();
        return $result;
    }
    
    function get($visitID)
    {
        $query = $this->con->prepare("SELECT * FROM statements INNER JOIN skinds ON skinds.id = statements.kind WHERE visitID = ?");
        $query->bind_param("i", $visitID);
        $query->execute();
        $result = $query->get_result();
        $query->close();
        return $result;
    }
    
    function getNotes($patientId)
    {
        $notes = false;
        if ($result = $this->con->query("SELECT statements.id AS statementId, statements.text FROM statements INNER JOIN visits ON visits.id=statements.visitID INNER JOIN episodes ON episodes.id=visits.episodeID WHERE episodes.name = \"Notes\" AND patientID = " . $patientId . " ORDER BY statements.id DESC")) {
            $notes = $result->fetch_assoc();
            $result->free_result();
        }
        return $notes;
    }

    function getStatements($kinds)
    {
        $first = true;
        $qText = "SELECT * FROM statements WHERE (";
        foreach ($kinds as $kind) {
            if (!$first) $qText .= " OR ";
            $qText .= "statements.kind=";
            $qText .= $this->skinds->get($kind);
            $first = false;
        }
        $qText .= ") ORDER BY text";
        if ($result = $this->con->query($qText)) {
            $statements = $result->fetch_all(MYSQLI_ASSOC);
            $result->free_result();
        }
        return $statements;
    }

    function getPatientStatements($patientId, $kinds, $codes=false, $sortOnEpisode=false)
    {
        $first = true;
        $qText = "SELECT statements.id, statements.text, statements.importance, visits.date, episodes.active AS active, episodes.id AS episodeId, episodes.name AS episodeName FROM statements INNER JOIN visits ON visits.id=statements.visitID INNER JOIN episodes ON episodes.id=visits.episodeID WHERE episodes.patientID=" . $patientId . " AND (";
        foreach ($kinds as $kind) {
            if (!$first) $qText .= " OR ";
            $qText .= "statements.kind=";
            $qText .= $this->skinds->get($kind);
            $first = false;
        }
        $qText .= ") ";
        
        if ($codes) {
            $qText .= " AND (";
            $multiple = false;
            foreach ($codes as $code) {
                if ($multiple) $qText .= " OR ";
                $qText .= "statements.text LIKE \"" . $code . "\"";
                $multiple = true;
            }
            $qText .= ") ";
        }
        $qText .= "ORDER BY ";
        if ($sortOnEpisode) $qText .= "episodes.enddate DESC, ";
        $qText .= "visits.date DESC, statements.id DESC";
        if ($result = $this->con->query($qText)) {
            $statements = $result->fetch_all(MYSQLI_ASSOC);
            $result->free_result();
        }
        return $statements;
    }
}


class Statement extends Record {
    public $id;
    public $visitId;
    public $kindId;
    public $importance;
    public $text;

    function __construct($con, $id)
    {
        parent::__construct($con, "statements", $id);
        $this->id = $id;
        $this->visitId = 0;
        $this->importance = 0;
        $this->kindId = 0;
        $this->text = "";
        if ($row = $this->get()) {
            $this->importance = $row['importance'];
            $this->visitId = $row['visitID'];
            $this->kindId = $row['kind'];
            $this->text = $row['text'];
        }
    }
    
    function import()
    {
        $query = $this->con->prepare("INSERT INTO statements (visitID, importance, kind, text) VALUES(?, ?, ?, ?)");
        $query->bind_param("iiis", $this->visitId, $this->importance, $this->kindId, $this->text);
        if ($query->execute()) {
            $this->id = $query->insert_id;
        }
        $query->close();
    }
    
    function update()
    {
        if ($this->id == 0) {
            $this->import();
        }
        else {
            $query = $this->con->prepare("UPDATE statements SET visitID = ?, importance = ?, kind = ?, text = ? WHERE id = ?");
            $query->bind_param("iiisi", $this->visitId, $this->importance, $this->kindId, $this->text, $this->id);
            $query->execute();
            $query->close();
        }
    }
}

?>
