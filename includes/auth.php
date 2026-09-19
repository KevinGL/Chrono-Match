<?php

session_start();

require_once "../config/db.php";

if(!isset($_POST["csrf"]) || $_SESSION["csrf"] !== $_POST["csrf"])
{
    header("location: /error.php?code=403");
}

unset($_SESSION["csrf"]);

$sth = $pdo->prepare("SELECT * FROM users WHERE email=:email");
$sth->execute(['email' => $_POST["email"]]);

$res = $sth->fetch();

if(!$res)
{
    $_SESSION["flash"] = ["type" => "error", "content" => "Utilisateur introuvable"];
    header("location: /login.php");
}

else
if(!password_verify($_POST["password"], $res["password"]))
{
    $_SESSION["flash"] = ["type" => "error", "content" => "Mot de passe incorrect"];
    header("location: /login.php");
}

else
{
    $_SESSION["SESSIONID"] = createToken();
    $_SESSION["user"] = ["username" => $res["username"], "email" => $res["email"], "phone" => $res["phone"]];
    header("location: /home.php");
}