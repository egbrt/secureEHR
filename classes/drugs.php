<?php

require_once "./classes/table.php";

class Drugs extends Table {
    
    function __construct($con)
    {
        $this->con = $con;
        $this->name = "drugs";
    }


    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `drugs` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `icpc` varchar(20) NOT NULL,
                    `prescription` varchar(200) NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
        }
        else {
            echo "TABLE drugs: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }


    function add($icpc, $prescription)
    {
        $existing = true;
        $query = $this->con->prepare("SELECT * FROM drugs WHERE icpc = ? AND prescription = ?");
        $query->bind_param("ss", $icpc, $prescription);
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $existing = ($result->num_rows != 0);
            }
        }
        $query->close();
    
        if (!$existing) {
            $query = $this->con->prepare("INSERT INTO drugs (icpc, prescription) VALUES(?, ?)");
            $query->bind_param("ss", $icpc, $prescription);
            $query->execute();
            $query->close();
        }
    }


    function find($icpc)
    {
        $drugs = array();
        $query = $this->con->prepare("SELECT prescription FROM drugs WHERE icpc = ?");
        $query->bind_param("s", $icpc);
        if ($query->execute()) {
            if ($result = $query->get_result()) {
                $drugs = $result->fetch_all(MYSQLI_ASSOC);
            }
        }
        $query->close();
        return $drugs;
    }
}

?>
