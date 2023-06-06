<?php

class Record {
    public $id;
    protected $con;
    protected $table;
    
    function __construct($con, $table, $id)
    {
        $this->id = $id;
        $this->con = $con;
        $this->table = $table;
    }

    function get()
    {
        $row = array();
        if ($this->id != 0) {
            $query = $this->con->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
            $query->bind_param("i", $this->id);
            if ($query->execute()) {
                $row = $query->get_result()->fetch_assoc();
            }
            $query->close();
        }
        return $row;
    }

    function delete()
    {
        $query = $this->con->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        $query->bind_param("i", $this->id);
        $query->execute();
        $query->close();
        $this->id = 0;
    }

}
?>
