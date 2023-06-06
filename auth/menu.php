<?php
require_once "login.php";
?>

<div class="menu">
<?php
if (isAuthenticated($con, $user, $name, $role)) {
    echo "<a href=\"admin.php\">Users</a>&nbsp;&nbsp";
    echo "<a href=\"../upload.php\">Upload ClaML</a>&nbsp;&nbsp";
    echo "<a href=\"logout.php\">Logout</a>&nbsp;&nbsp";
}
?>
</div>
