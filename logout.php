<?php

session_start();

unset($_SESSION["SESSIONID"]);
header("location: login.php");