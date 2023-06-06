<?php
/*
    HIS
    Copyright (c) 2021-2022, Egbert van der Haring en Kees van Boven

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/


function showMenu($user, $role)
{
    echo "<div id=\"menu\" class=\"menu\"><ul>";
    if ($role == "admin") {
        echo "<li><a href=\"auth/admin.php\">Users</a></li>";
        echo "<li><a href=\"storage.php\">Storage</a></li>";
        echo "<hr/>";
    }
    echo "<li><a href=\"index.php?page=patients\">Patients</a></li>";
    //echo "<li><a href=\"index.php?page=calendar\">Calendar</a></li>";
    echo "<li><a href=\"index.php?page=reports\">Reports</a></li>";
    echo "<hr/>";
    echo "<li><a href=\"settings.php\">Settings</a></li>";
    echo "<li><a href=\"auth/logout.php\">Logout</a></li>";
    echo "<hr/>";
    //echo "<li><a href=\"faq.php\">FAQ</a></li>";
    echo "</ul></div>";
}

?>
