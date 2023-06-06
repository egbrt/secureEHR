<?php

class Table {
    protected $con;
    public $name;

    function __construct($con, $name)
    {
        $this->con = $con;
        $this->name = $name;
    }
    
    function drop()
    {
        if ($this->exists()) {
            $query = "DROP TABLE " . $this->name;
            mysqli_query($this->con, $query);
        }
    }
    
    function exists()
    {
        $valid = false;
        if ($result = $this->con->query("SHOW TABLES LIKE '" . $this->name . "'")) {
            if ($result->num_rows == 1) {
                $valid = true;
            }
            $result->free_result();
        }
        return $valid;
    }
        
    function getAll()
    {
        $rows = false;
        if ($result = $this->con->query("SELECT * FROM " . $this->name)) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $result->free_result();
        }
        return $rows;
    }
}

?>
