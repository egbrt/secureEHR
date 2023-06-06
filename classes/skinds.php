<?php

if (!defined("PORTAL")) define("PORTAL", false);
if (PORTAL) {
    require_once "../classes/table.php";
}
else {
    require_once "classes/table.php";
}


class SKinds extends Table {

    function __construct($con)
    {
        $this->con = $con;
        $this->name = "skinds";
    }
    
    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `skinds` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `name` varchar(40) NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
            $query = $this->con->prepare("INSERT INTO skinds (name) VALUES(?)");
            $query->bind_param("s", $name);
            $name = "Notes";       $query->execute();
            $name = "Reason";      $query->execute();
            $name = "Subjective";  $query->execute();
            $name = "Objective";   $query->execute();
            $name = "Assessment";  $query->execute();
            $name = "Plan";        $query->execute();
            $name = "Medication";  $query->execute();
            $name = "Laboratory";  $query->execute();
            $name = "Surgery";     $query->execute();
            $query->close();
        }
        else {
            echo "TABLE skinds: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }

    function get($name)
    {
        $kind = 0;
        $query = $this->con->prepare("SELECT * FROM skinds WHERE name = ?");
        $query->bind_param("s", $name);
        if ($query->execute()) {
            $result = $query->get_result();
            if ($row = $result->fetch_assoc()) {
                $kind = $row['id'];
            }
        }
        $query->close();
    
        if ($kind == 0) {
            $query = $this->con->prepare("INSERT INTO skinds (name) VALUES(?)");
            $query->bind_param("s", $name);
            if ($query->execute()) {
                $kind = $query->insert_id;
            }
            $query->close();
        }
        return $kind;
    }
    
    function getName($skind)
    {
        $name = "unknown";
        $query = $this->con->prepare("SELECT * FROM skinds WHERE id = ?");
        $query->bind_param("i", $skind);
        if ($query->execute()) {
            $result = $query->get_result();
            if ($row = $result->fetch_assoc()) {
                $name = $row["name"];
            }
        }
        return $name;
    }
}
?>
