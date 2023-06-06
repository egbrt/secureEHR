<?php

class Uploader {
    public $errors;
    public $file;
    private $ext;
    private $patientId;
    private $episodeId;
    private $visitId;

    function __construct($extension)
    {
        $this->errors = "";
        $this->file = "";
        $this->ext = $extension;
        $this->patientId = 0;
        $this->episodeId = 0;
    }

    function uploadFile()
    {
        $valid = false;
        if (basename($_FILES["fileToUpload"]["name"])) {
            $dir = "uploads/";
            $this->file = $dir . basename($_FILES["fileToUpload"]["name"]);
            $type = strtolower(pathinfo($this->file,PATHINFO_EXTENSION));
            if (file_exists($this->file)) {
                unlink($this->file);
            }
            if (!file_exists($this->file)) {
                if ($type == "xml") {
                    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $this->file)) {
                        $valid = true;
                    } else {
                        $this->errors = "Sorry, there was an error uploading your file.";
                    }
                }
                else {
                    $this->errors = "Only files with extension " . $this->ext . "allowed.";
                }
            }
            else {
                $this->errors = "file already exists, and could not be deleted.";
            }
        }
        else {
            $this->errors = "Please choose a file first.";
        }
        return $valid;
    }
    
    function parseXML($con)
    {
        $this->errors = '';
        $valid = true;
        $patientId = 0;
        $secpml = new XMLReader;
        $secpml->open($this->file);
        libxml_use_internal_errors(true);
        if ($this->isSecPML($secpml)) {
            while ($valid and $secpml->read()) {
                if (($secpml->depth == 1) and ($secpml->nodeType == XMLReader::ELEMENT)) {
                    if ($secpml->name == "Patient") {
                        $valid = $this->addPatient($con, $secpml);
                    }
                    elseif ($secpml->name == "Episode") {
                        $this->addEpisode($con, $secpml);
                    }
                }
            }
        }
        else {
            $this->errors = "This is not a valid SecPML file.<br/>";
            $valid = false;
        }
        foreach(libxml_get_errors() as $error) {
            $this->errors .= 'At line: ' . $error->line . ', column: ' . $error->column . ': ' . $error->message . '<br/>';
            $valid = false;
        }
        libxml_use_internal_errors(false);
        $secpml->close();
        return $valid;
    }
    
    private function isSecPML($secpml)
    {
        $valid = false;
        while (!$valid and $secpml->read() and ($secpml->depth == 0)) {
            $valid = (($secpml->nodeType == XMLReader::ELEMENT) and ($secpml->name == 'SecPML'));
        }
        return $valid;
    }
    
    private function addPatient($con, $secpml)
    {
        $valid = true;
        $patient = new Patient($con, 0);
        while ($valid and $secpml->read()) {
            if ($secpml->nodeType == XMLReader::END_ELEMENT) {
                if ($secpml->name == "Patient") {
                    $valid = false;
                }
            }
            elseif ($secpml->nodeType == XMLReader::ELEMENT) {
                if ($secpml->name == "Label") {
                    if ($secpml->read()) $patient->label = $secpml->value;
                }
                elseif ($secpml->name == "Email") {
                    if ($secpml->read()) $patient->email = $secpml->value;
                }
                elseif ($secpml->name == "Info") {
                    if ($secpml->read()) $patient->info = $secpml->value;
                }
            }            
        }
        if ($patient->import()) {
            $this->patientId = $patient->id;
            $valid = true;
        }
        else {
            $this->errors = "The patient could not be added, it may be that this patient is already present in the database.";
            $valid = false;
        }
        return $valid;
    }
    
    private function addEpisode($con, $secpml)
    {
        $valid = true;
        $episode = new Episode($con, 0);
        $episode->patientId = $this->patientId;
        $episode->active = $secpml->getAttribute("active");
        $episode->startdate = $secpml->getAttribute("start");
        $episode->enddate = $secpml->getAttribute("last");
        $episode->name = html_entity_decode($secpml->getAttribute("name"));
        
        if ($episode->import()) {
            $this->episodeId = $episode->id;
            while ($valid and $secpml->read()) {
                if ($secpml->nodeType == XMLReader::END_ELEMENT) {
                    if ($secpml->name == "Episode") {
                        $valid = false;
                    }
                }
                elseif ($secpml->nodeType == XMLReader::ELEMENT) {
                    if ($secpml->name == "Visit") {
                        $this->addVisit($con, $secpml);
                    }
                }            
            }
        }
    }
    
    private function addVisit($con, $secpml)
    {
        $valid = true;
        $visit = new Visit($con, 0);
        $visit->episodeId = $this->episodeId;
        $visit->date = $secpml->getAttribute("date");
        if ($visit->import()) {
            $this->visitId = $visit->id;
            while ($valid and $secpml->read()) {
                if ($secpml->nodeType == XMLReader::END_ELEMENT) {
                    if ($secpml->name == "Visit") {
                        $valid = false;
                    }
                }
                elseif ($secpml->nodeType == XMLReader::ELEMENT) {
                    if ($secpml->name == "Statement") {
                        $this->addStatement($con, $secpml);
                    }
                }
            }            
        }
    }
    
    private function addStatement($con, $secpml)
    {
        $valid = true;
        $skinds = new SKinds($con);
        $statement = new Statement($con, 0);
        $statement->visitId = $this->visitId;
        $statement->kindId = $skinds->get($secpml->getAttribute("kind"));
        $statement->importance = $secpml->getAttribute("importance");
        if ($statement->importance == null) $statement->importance = 0;
        while ($valid and $secpml->read()) {
            if ($secpml->nodeType == XMLReader::END_ELEMENT) {
                if ($secpml->name == "Statement") {
                    $valid = false;
                }
            }
            elseif ($secpml->nodeType == XMLReader::ELEMENT) {
            }            
            elseif ($secpml->nodeType == XMLReader::TEXT) {
                $statement->text = html_entity_decode($secpml->value);
            }            
        }
        $statement->import();
    }
}

?>
