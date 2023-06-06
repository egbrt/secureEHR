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

require_once "auth/login.php";
startSecureSession();
?>

<!DOCTYPE html>
<html>
<head>
    <?php include "title.html" ?>
    <link type="text/css" rel="stylesheet" href="auth/auth.css" />
    <link type="text/css" rel="stylesheet" href="styles/main.css" />
    <link type="text/css" rel="stylesheet" href="styles/icpc3.css" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <script src="scripts/jquery.min.js"></script>
    <script src="scripts/main.js" type="module"></script>
</head>

<body>

<div class="header">
    <h1>ICPC secureEHR</h1>
    <?php include "version.html" ?>
    <img class="menuButton" id="menuButton" src="images/menu.png" alt="Menu"/>
</div>
<div class="main">

