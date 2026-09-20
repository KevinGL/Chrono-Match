<?php

session_start();

if(isset($_SESSION["user"]))
{
    header("location: home.php");
    exit();
}

if(isset($_SESSION["flash"]))
{
    echo "<p>" . $_SESSION["flash"]["content"] . "</p>";
    unset($_SESSION["flash"]);
}

require_once "includes/functions.php";

if (empty($_SESSION["csrf"]))
{
    $_SESSION["csrf"] = createToken();
}

$token = $_SESSION["csrf"];

?>

<form method="POST" action="includes/auth.php">

    <input type="email" name="email" required />
    <input type="password" name="password" required />
    <input type="hidden" name="csrf" value="<?= $token ?>" />
    <input type="submit" value="Se connecter" />

</form>