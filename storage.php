<?php

include "templates/header.php";
require_once "templates/menu.php";
require_once "auth/user.php";
require_once "classes/database.php";
require_once "classes/uploader.php";
require_once "classes/dis.php";

if (isAuthenticated($con, $user, $name, $role)) {
    showMenu($user, $role);
    if (isset($_REQUEST['submit'])) {
        $action = $_REQUEST['submit'];
        if ($action == "Reset database") {
            $database = new Database($con);
            $database->recreate();
            echo "<p>Database has been wiped clean.</p>";
        }
        elseif ($action == "Load DIS") {
            $dis = new Dis($con);
            $uploader = new Uploader("csv");
            if ($uploader->uploadFile()) {
                $dis->import($uploader->file);
            }
        }
    }
    else {
?>
        <div id="storageRecreateDB">
        <form method="post">
        <p>By pressing the button, all data will be wiped!</p>
        <input type='submit' value='Reset database' name='submit'>
        </form>
        </div>
        
        <div id="storageLoadDIS">
        <form method='post' enctype='multipart/form-data'>
        <p>Load Diagnosis-Intervention-System with billing information.</p>
        <p>The input must be a Comma Separated Value file with three columns:
        <ol>
        <li>The first column contains the diagnosis code.</br>
        This may also be the chapter or component code</li>
        <li>The second colum contains the intervention code.</li>
        <li>The third column contains the billing code or price.</li>
        </ol>
        <p>NB: The first line is ignored, as it is assumed that this contains the name of the columns.</p>
        <p><input type='file' name='fileToUpload' id='fileToUpload'></p>
        <input type='submit' value='Load DIS' name='submit'>
        </form>
        </div>
<?php
    }
    mysqli_close($con);
}
include "templates/footer.php";

?>
