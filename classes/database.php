<?php

require_once "classes/patients.php";
require_once "classes/episodes.php";
require_once "classes/visits.php";
require_once "classes/statements.php";
require_once "classes/skinds.php";
require_once "classes/drugs.php";
require_once "classes/appointments.php";
require_once "classes/dis.php";
require_once "classes/bills.php";

class Database {
    private $con;
    private $patients;
    private $episodes;
    private $visits;
    private $statements;
    private $skinds;
    private $drugs;
    private $appointments;
    private $dis;
    private $bills;

    function __construct($con)
    {
        $this->con = $con;
        $this->patients = new Patients($con);
        $this->episodes = new Episodes($con);
        $this->visits = new Visits($con);
        $this->statements = new Statements($con);
        $this->skinds = new SKinds($con);
        $this->drugs = new Drugs($con);
        $this->appointments = new Appointments($con);
        $this->dis = new Dis($con);
        $this->bills = new Bills($con);
    }
    
    function isEmpty()
    {
        $empty = true;
        $query = "SHOW TABLES LIKE 'patients'";
        if ($result = mysqli_query($this->con, $query)) {
            if ($row = mysqli_fetch_array($result)) {
                $empty = false;
            }
            mysqli_free_result($result);
        }
        return $empty;
    }

    function recreate()
    {
        $this->dropTables();
        $this->createTables();
    }

    private function dropTables()
    {
        $this->patients->drop();
        $this->episodes->drop();
        $this->visits->drop();
        $this->statements->drop();
        $this->skinds->drop();
        $this->drugs->drop();
        $this->appointments->drop();
        $this->dis->drop();
        $this->bills->drop();
    }

    private function createTables()
    {
        $valid = (    $this->patients->create()
                  and $this->episodes->create()
                  and $this->visits->create()
                  and $this->statements->create()
                  and $this->skinds->create()
                  and $this->drugs->create()
                  and $this->appointments->create()
                  and $this->dis->create()
                  and $this->bills->create());
        return $valid;
    }
}

?>
