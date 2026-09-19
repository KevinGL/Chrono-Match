<?php

session_start();

if(!isset($_SESSION["SESSIONID"]))
{
    header("location: login.php");
}

else
{
    header("location: home.php");
}

?>