<?php

class CodeGenerator {
    public $code;
    
    function __construct($length)
    {
        $i = 0;
        $this->code = "";
        while ($i < $length) {
            $this->code .= rand(0,9);
            $i++;
        }
    }
}

?>
