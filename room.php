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

$jwt = generateJWT(["username" => $_SESSION["user"]["username"], "gender" => $_SESSION["user"]["gender"], "search" => $_SESSION["user"]["search"]], readEnv("JWT_KEY"));

?>

<script>
    const jwtToken = <?= json_encode($jwt) ?>;
    const socket = new WebSocket(`ws://localhost:8080?token=${encodeURIComponent(jwtToken)}`);

    socket.onopen = () =>
    {
        socket.send(JSON.stringify({type: 'connect', jwt: '<?= $jwt ?>'}));
    }
</script>