<?php

if (!defined("PORTAL")) define("PORTAL", false);
if (PORTAL) {
    require_once "../classes/table.php";
    require_once "../classes/record.php";
    require_once "../classes/crypto.php";
}
else {
    require_once "classes/table.php";
    require_once "classes/record.php";
    require_once "classes/crypto.php";
}


class Patients extends Table {
    
    function __construct($con)
    {
        $this->con = $con;
        $this->name = "patients";
    }

    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `patients` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `label` varchar(255) NOT NULL,
                    `email` varchar(255),
                    `info` text
                )";
        if (mysqli_query($this->con, $query)) {
            $query = "ALTER TABLE `patients`
                        ADD KEY `email` (`email`)";
            if (!mysqli_query($this->con, $query)) {
                echo "TABLE patients: " . mysqli_error($this->con);
                $valid = false;
            }
        }
        else {
            echo "TABLE patients: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }
    
    function getNumber()
    {
        $number = 0;
        if ($results = $this->con->query("SELECT COUNT(*) FROM patients")) {
            if ($row = $results->fetch_array()) {
                $number = $row[0];
            }
        }
        return $number;
    }
    
    function search($crypto)
    {
        $patient = false;
        $query = $this->con->prepare("SELECT * FROM patients WHERE label = ?");
        $query->bind_param("s", $crypto->label);
        if ($query->execute() && ($result = $query->get_result())) {
            if ($row = $result->fetch_assoc()) {
                $patient = new Patient($this->con, $row['id']);
                $patient->label = $crypto->id;
                $patient->email = $crypto->decrypt($row['email']);
                $patient->info = $crypto->decrypt($row['info']);
                if (!$patient->email) {
                    $patient = false;
                }
            }
        }
        $query->close();
        return $patient;
    }
}


class Patient extends Record {
    public $id;
    public $label;
    public $email;
    public $info;
    
    function __construct($con, $id)
    {
        parent::__construct($con, "patients", $id);
        $this->id = $id;
        $this->label = "";
        $this->email = "";
        $this->info = "";
    }
    
    function get()
    {
        if ($row = parent::get()) {
            $this->label = $row['label'];
            $this->email = $row['email'];
            $this->info = $row['info'];
        }
    }
    
    function import()
    {
        $valid = false;
        $absent = true;
        $query = $this->con->prepare("SELECT * FROM patients WHERE label = ?");
        $query->bind_param("s", $this->label);
        if ($query->execute() && ($result = $query->get_result())) {
            if ($row = $result->fetch_assoc()) {
                $absent = ($row['id'] == 0);
            }
        }
        $query->close();

        if ($absent) {
            $query = $this->con->prepare("INSERT INTO patients (label, email, info) VALUES (?, ?, ?)");
            $query->bind_param("sss", $this->label, $this->email, $this->info);
            if ($query->execute()) {
                $this->id = $query->insert_id;
                $valid = true;
            }
            $query->close();
        }
        return $valid;
    }
    
    function saveChanges($crypto, $email, $info)
    {
        $this->label = $crypto->id;
        $this->email = $email;
        $this->info = $info;
        $en_email = $crypto->encrypt($email);
        $en_info = $crypto->encrypt($info);
        if ($this->id == 0) {
            $query = $this->con->prepare("INSERT INTO patients (label, email, info) VALUES (?, ?, ?)");
            $query->bind_param("sss", $crypto->label, $en_email, $en_info);
            if ($query->execute()) {
                $this->id = $query->insert_id;
            }
            else {
                //echo mysqli_error($this->con);
            }
        }
        else {
            $query = $this->con->prepare("UPDATE patients SET label = ?, email = ?, info = ? WHERE id = ?");
            $query->bind_param("sssi", $crypto->label, $en_email, $en_info, $this->id);
            $query->execute();
        }
        $query->close();
    }
}

?>
