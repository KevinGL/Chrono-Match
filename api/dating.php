<?php

require_once "../config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    echo json_encode(["code" => 401, "content" => "Not authenticated"]);
    exit();
}

if(!isset($_GET["contact"]))
{
    echo json_encode(["code" => 400, "content" => "Bad request"]);
    exit();
}

$headers = getallheaders();
$clientToken = $headers['X-CSRF-TOKEN'] ?? "";

if($clientToken === "" || $clientToken !== $_SESSION["csrf_token"])
{
    echo json_encode(["code" => 403, "content" => "Forbidden"]);
    exit();
}

$id = decryptId($_GET["contact"]);

$now = new DateTime();
$now->setTimezone(new DateTimeZone("Europe/Paris"));

$th = $pdo->prepare("INSERT INTO datings (user_id1, user_id2, date) VALUES (:user1, :user2, :date)");
$res = $th->execute(["user1" => $_SESSION["user"]["id"], "user2" => $id, "date" => $now->format("Y-m-d H:i:s")]);

if(!$res)
{
    echo json_encode(["code" => 500, "content" => "Internal error"]);
    exit();
}

echo json_encode(["code" => 200, "content" => "ok"]);
exit();