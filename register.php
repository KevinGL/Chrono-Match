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
$createdAt = new DateTime();

$date->setTimezone(new DateTimeZone("Europe/Paris"));
$createdAt->setTimezone(new DateTimeZone("Europe/Paris"));

$date->setTimestamp((int)$_GET["session"]);

$sth = $pdo->prepare("INSERT INTO inscriptions (user_id, date, created_at) VALUES (:user_id, :date, :created_at)");
$sth->execute([
    'user_id'    => $_SESSION["user"]["id"],
    'date'       => $date->format("Y-m-d H:i:s"),
    'created_at' => $createdAt->format("Y-m-d H:i:s")
]);

$_SESSION["flash"] = ["type" => "success", "content" => "Inscription validée, une inscription vous engage à être présent"];

header("location: sessions.php");