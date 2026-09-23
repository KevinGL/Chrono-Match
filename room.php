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

<ul id="messages"></ul>

<form hidden id="form">
    <textarea id="message"></textarea>
    <input type="submit" value="Envoyer" />
</form>

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
            document.getElementById("form").hidden = false;
        }

        else
        if(data.status === "receive")
        {
            const li = document.createElement("li");
            li.innerText = `${data.username} : ${data.message}`;
            document.getElementById("messages").appendChild(li);
        }
    }

    document.getElementById("form").addEventListener("submit", (e) =>
    {
        e.preventDefault();
        
        const message = document.getElementById("message").value;

        socket.send(JSON.stringify({type: "message", message}));

        document.getElementById("message").value = "";

        const li = document.createElement("li");
        li.innerText = `Vous : ${message}`
        document.getElementById("messages").appendChild(li);
    });
</script>