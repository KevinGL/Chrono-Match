<?php

session_start();

if(!isset($_SESSION["SESSIONID"]))
{
    header("location: login.php");
}

?>

BIENVENUE <?= $_SESSION["user"]["username"] ?> !