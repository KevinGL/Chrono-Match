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

$th = $pdo->prepare("INSERT INTO likes (sender, receiver) VALUES (:sender, :receiver)");
$res = $th->execute(["sender" => $_SESSION["user"]["id"], "receiver" => $id]);

if(!$res)
{
    echo json_encode(["code" => 500, "content" => "Internal error"]);
    exit();
}

$th = $pdo->prepare("SELECT * FROM likes WHERE receiver=:receiver");
$th->execute(["receiver" => $_SESSION["user"]["id"]]);
$likesRecip = $th->fetch();

$th = $pdo->prepare("SELECT * FROM matchs WHERE user_id1=:user1 AND user_id2=:user2 OR user_id1=:user2 AND user_id2=:user1");
$th->execute(["user1" => $_SESSION["user"]["id"], "user2" => $id]);
$exists = $th->fetch();

if($likesRecip && !$exists)
{
    $th = $pdo->prepare("INSERT INTO matchs (user_id1, user_id2) VALUES (:user_id1, :user_id2)");
    $res = $th->execute(["user_id1" => $_SESSION["user"]["id"], "user_id2" => $id]);
}

echo json_encode(["code" => 200, "content" => "ok"]);