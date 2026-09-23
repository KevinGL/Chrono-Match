<?php

require_once "config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

if(!validRoom($pdo))
{
    header("location: home.php");
    exit();
}

$jwt = generateJWT(["id" => $_SESSION["user"]["id"], "username" => $_SESSION["user"]["username"], "gender" => $_SESSION["user"]["gender"], "search" => $_SESSION["user"]["search"]], readEnv("JWT_KEY"));

?>

<?php require_once "includes/header.php" ?>

<div id="status"></div>

<?php require_once "includes/footer.php" ?>

<script>
    const jwtToken = <?= json_encode($jwt) ?>;
    const socket = new WebSocket(`ws://localhost:8080?token=${encodeURIComponent(jwtToken)}`);

    socket.onmessage = (res) =>
    {
        const data = JSON.parse(res.data);
        console.log(data);

        if(data.status === "waiting")
        {
            document.getElementById("status").innerHTML = "Patientez nous vous mettons en relation avec quelqu'un ...";
        }

        else
        if(data.status === "contact")
        {
            document.getElementById("status").innerHTML = `Voici ${data.contact.username}`;
        }
    }
</script>