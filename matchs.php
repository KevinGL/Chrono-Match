<?php

require_once "config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    echo json_encode(["code" => 401, "content" => "Not authenticated"]);
    exit();
}

$th = $pdo->prepare("SELECT * FROM matchs m JOIN users u ON m.user_id1=u.id WHERE m.user_id2=:user");
$th->execute(["user" => $_SESSION["user"]["id"]]);
$matchs = $th->fetchAll();

$th = $pdo->prepare("SELECT * FROM matchs m JOIN users u ON m.user_id2=u.id WHERE m.user_id1=:user");
$th->execute(["user" => $_SESSION["user"]["id"]]);
$matchs = array_merge($matchs, $th->fetchAll());

require_once "includes/header.php";

echo "<ul>";

foreach($matchs as $match)
{
    echo "<li>" . htmlspecialchars($match["username"]) . "</li>";
}

echo "</ul>";

require_once "includes/footer.php";