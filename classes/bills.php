<?php
require_once "./classes/table.php";

class Bills extends Table {
    
    function __construct($con)
    {
        $this->con = $con;
        $this->name = "bills";
    }


    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `bills` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `patientID` int(11) NOT NULL,
                    `date` date NOT NULL,
                    `diagnosis` varchar(400) NOT NULL,
                    `intervention` varchar(400) NOT NULL,
                    `billing` varchar(20) NOT NULL,
                    `paid` tinyint(1) NOT NULL DEFAULT 0
                )";
        if (mysqli_query($this->con, $query)) {
        }
        else {
            echo "TABLE bills: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }

    function add($patient, $date, $diagnosis, $intervention, $billing)
    {
        $query = $this->con->prepare("INSERT INTO bills (patientID, date, diagnosis, intervention, billing) VALUES(?, ?, ?, ?, ?)");
        $query->bind_param("issss", $patient, $date, $diagnosis, $intervention, $billing);
        $query->execute();
        $query->close();
    }
    
    function getUnpaid()
    {
        $unpaids = false;
        if ($result = $this->con->query("SELECT bills.*, patients.label AS patient FROM bills INNER JOIN patients ON patients.id = bills.patientID WHERE paid = 0")) {
            $unpaids = $result->fetch_all(MYSQLI_ASSOC);
            $result->free_result();
        }
        return $unpaids;
    }
    
}

?>

