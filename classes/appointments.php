<?php
require_once "classes/table.php";


class Appointments extends Table {

    function __construct($con)
    {
        $this->con = $con;
        $this->name = "appointments";
    }

    function create()
    {
        $valid = true;
        $query = "CREATE TABLE `appointments` (
                    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    `patientID` int(11) NOT NULL,
                    `date` date NOT NULL,
                    `starttime` time NOT NULL,
                    `endtime` time NOT NULL
                )";
        if (mysqli_query($this->con, $query)) {
            /* nothing to add */
        }
        else {
            echo "TABLE appointments: " . mysqli_error($this->con);
            $valid = false;
        }
        return $valid;
    }

    function add($patientId, $date, $starttime, $endtime)
    {
        $query = $this->con->prepare("INSERT INTO appointments (patientID, date, starttime, endtime) VALUES (?, ?, ?, ?)");
        $query->bind_param("isss", $patientId, $date, $starttime, $endtime);
        $query->execute();
        $query->close();
    }
    
    function delete($id)
    {
        $query = $this->con->prepare("DELETE FROM appointments WHERE id = ?");
        $query->bind_param("i", $id);
        $query->execute();
        $query->close();
    }

    function get($date)
    {
        $appointments = array();
        $query = $this->con->prepare("SELECT appointments.id, appointments.patientID, appointments.date, appointments.starttime, appointments.endtime, patients.label FROM appointments INNER JOIN patients ON patients.id = appointments.patientId WHERE date = ? ORDER BY starttime");
        $query->bind_param("s", $date);
        $query->execute();
        if ($result = $query->get_result()) {
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $appointments[$i]['id'] = $row['id'];
                $appointments[$i]['date'] = $row['date'];
                $appointments[$i]['starttime'] = $row['starttime'];
                $appointments[$i]['endtime'] = $row['endtime'];
                $appointments[$i]['patientId'] = $row['patientID'];
                $appointments[$i]['patientLabel'] = $row['label'];
                $i++;
            }
        }
        $query->close();
        return $appointments;
    }
}

?>
