<?php

require_once "./classes/table.php";

class Dis extends Table {
    
    function __construct($con)
    {
        $this->con = $con;
        $this->name = "dis";
    }


    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `dis` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `diagnosis` varchar(20) NOT NULL,
                    `intervention` varchar(20) NOT NULL,
                    `billing` varchar(20) NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
        }
        else {
            echo "TABLE dis: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }
    
    
    function import($file)
    {
        if ($data = fopen($file,"r")) {
            $this->con->query("TRUNCATE TABLE dis");
            $query = $this->con->prepare("INSERT INTO dis (diagnosis, intervention, billing) VALUES (?, ?, ?)");
            $query->bind_param("sss", $diagnosis, $intervention, $billing);
            fgetcsv($data); // ignore first line
            while ($line = fgetcsv($data)) {
                $diagnosis = $line[0];
                $intervention = $line[1];
                $billing = $line[2];
                $query->execute();
            }
            $query->close();
        }
    }
    

    function getInterventions($dx)
    {
        $interventions = false;
        if ($dot = strpos($dx, '.')) {
            $query = $this->con->prepare("SELECT intervention FROM dis WHERE (diagnosis = ? OR diagnosis = ? OR diagnosis = ? OR diagnosis = ?)");
            $query->bind_param("ssss", $dx, $maindx, $component, $chapter);
            $maindx = substr($dx, 0, $dot);
        }
        else {
            $query = $this->con->prepare("SELECT intervention FROM dis WHERE (diagnosis = ? OR diagnosis = ? OR diagnosis = ?)");
            $query->bind_param("sss", $dx, $component, $chapter);
        }
        $component = substr($dx, 0, 2);
        $chapter = substr($dx, 0, 1);
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $interventions = $result->fetch_all(MYSQLI_ASSOC);
            }
        }
        $query->close();
        return $interventions;
    }
    
    
    function getBilling($dx, $intervention)
    {
        $billing = false;
        if ($dot = strpos($dx, '.')) {
            $query = $this->con->prepare("SELECT billing FROM dis WHERE (diagnosis = ? OR diagnosis = ? OR diagnosis = ? OR diagnosis = ?) AND intervention = ?");
            $query->bind_param("sssss", $dx, $maindx, $component, $chapter, $intervention);
            $maindx = substr($dx, 0, $dot);
        }
        else {
            $query = $this->con->prepare("SELECT billing FROM dis WHERE (diagnosis = ? OR diagnosis = ? OR diagnosis = ?) AND intervention = ?");
            $query->bind_param("ssss", $dx, $component, $chapter, $intervention);
        }
        $component = substr($dx, 0, 2);
        $chapter = substr($dx, 0, 1);
        if ($query->execute()) {
            if ($row = $query->get_result()->fetch_assoc()) {
                $billing = $row['billing'];
            }
        }
        $query->close();
        return $billing;
    }
    
}

?>
