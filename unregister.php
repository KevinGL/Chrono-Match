<?php

require_once __DIR__ . "/config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

if(!isset($_GET["session"]))
{
    header("location: sessions.php");
    exit();
}

$date = new DateTime();
$date->setTimezone(new DateTimeZone("Europe/Paris"));
$date->setTimestamp((int)$_GET["session"]);

$sth = $pdo->prepare("DELETE FROM inscriptions WHERE user_id=:user_id AND date=:created_at");
$sth->execute([
    'user_id'    => $_SESSION["user"]["id"],
    'created_at' => $date->format("Y-m-d H:i:s")
]);

header("location: sessions.php");