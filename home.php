<?php

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

?>

<?php require "includes/header.php" ?>

BIENVENUE <?= htmlspecialchars($_SESSION["user"]["username"]) ?> !

<?php require "includes/footer.php" ?>