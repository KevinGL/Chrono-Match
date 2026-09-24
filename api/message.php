<?php

require_once "../config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    echo json_encode(["code" => 401, "content" => "Not authenticated"]);
    exit();
}

$headers = getallheaders();
$clientToken = $headers['X-CSRF-TOKEN'] ?? "";

if($clientToken === "" || $clientToken !== $_SESSION["csrf_token"])
{
    echo json_encode(["code" => 403, "content" => "Forbidden"]);
    exit();
}

$jsonContent = file_get_contents('php://input');
$data = json_decode($jsonContent, true);

$sender = $_SESSION["user"]["id"];
$receiver = decryptId($data["receiver"]);
$content = htmlspecialchars($data["content"]);
$createdAt = new DateTime();
$createdAt->setTimezone(new DateTimeZone("Europe/Paris"));
$createdAt->setTimestamp($data["createAt"] / 1000);

$th = $pdo->prepare("INSERT INTO messages (sender, receiver, content, createdAt) VALUES (:sender, :receiver, :content, :createdAt)");
$res = $th->execute(["sender" => $sender, "receiver" => $receiver, "content" => $content, "createdAt" => $createdAt->format("Y-m-d H:m:s")]);

if(!$res)
{
    echo json_encode(["code" => 500, "content" => "Internal error"]);
    exit();
}

echo json_encode(["code" => 200, "content" => "ok"]);