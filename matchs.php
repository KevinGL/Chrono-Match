<?php

require_once "config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    echo json_encode(["code" => 401, "content" => "Not authenticated"]);
    exit();
}

require_once "includes/header.php";

if(!isset($_GET["match_id"]))
{
    $th = $pdo->prepare("SELECT m.id AS match_id, u.username FROM matchs m JOIN users u ON m.user_id1=u.id WHERE m.user_id2=:user");
    $th->execute(["user" => $_SESSION["user"]["id"]]);
    $matchs = $th->fetchAll();

    $th = $pdo->prepare("SELECT m.id AS match_id, u.username FROM matchs m JOIN users u ON m.user_id2=u.id WHERE m.user_id1=:user");
    $th->execute(["user" => $_SESSION["user"]["id"]]);
    $matchs = array_merge($matchs, $th->fetchAll());

    echo "<ul>";

    foreach($matchs as $match)
    {
        echo "<li><a href=\"matchs.php?match_id=" . encryptId($match["match_id"]) . "\">" . htmlspecialchars($match["username"]) . "</a></li>";
    }

    echo "</ul>";
}

else
{
    $matchId = decryptId($_GET["match_id"]);
    
    $th = $pdo->prepare("SELECT m.content, m.createdAt, u.username, u.id FROM messages m JOIN users u ON m.sender=u.id WHERE m.match_id=:match_id ORDER BY m.createdAt ASC");
    $th->execute(["match_id" => $matchId]);
    $messages = $th->fetchAll();

    $th = $pdo->prepare("SELECT u.id AS contact_id FROM messages m JOIN users u ON m.sender=u.id WHERE m.match_id=:match_id AND u.id<>:user_id LIMIT 1");
    $th->execute(["match_id" => $matchId, "user_id" => $_SESSION["user"]["id"]]);
    $contactId = encryptId($th->fetch()["contact_id"]);

    echo "<ul id=\"messages\">";

    foreach($messages as $message)
    {
        $date = new DateTime($message["createdAt"]);
        $date->setTimezone(new DateTimeZone("Europe/Paris"));
    
        echo "<li>" . htmlspecialchars($message["username"]) . " : " . htmlspecialchars($message["content"]) . " (Le " . $date->format("d-m-Y") . " à " . $date->format("H:m:s") . ")" . "</li>";
    }

    echo "</ul>";

    $jwt = generateJWT(["id" => $_SESSION["user"]["id"]], readEnv("JWT_KEY"));

    ?>

    <form id="form">
        <textarea id="message"></textarea>
        <input type="submit" value="Envoyer" />
    </form>

    <script>
        const jwtToken = <?= json_encode($jwt) ?>;
        const socket = new WebSocket(`ws://localhost:5000?token=${encodeURIComponent(jwtToken)}`);

        socket.onmessage = (res) =>
        {
            const data = JSON.parse(res.data);

            const li = document.createElement("li");
            li.innerText = `${data.username} : ${data.message}`;
            document.getElementById("messages").appendChild(li);
        }

        document.getElementById("form").addEventListener("submit", async (e) =>
        {
            e.preventDefault();

            const message = document.getElementById("message").value;

            socket.send(JSON.stringify({type: "message", message, contact: "<?= $contactId ?>", username: "<?= $_SESSION["user"]["username"] ?>"}));

            document.getElementById("message").value = "";

            const li = document.createElement("li");
            li.innerText = `<?= $_SESSION["user"]["username"] ?> : ${message}`;
            document.getElementById("messages").appendChild(li);

            //////////////////////////////////////

            await fetch("api/message.php", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": '<?= $_SESSION["csrf_token"] ?>', "Content-Type": "application/json" },
                body: JSON.stringify({
                    receiver: "<?= $contactId ?>",
                    content: message,
                    createAt: Date.now(),
                    matchId: "<?= $_GET["match_id"] ?>"
                })
            });
        });
    </script>

    <?php
}

require_once "includes/footer.php";