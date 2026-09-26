<?php

require_once "config/db.php";

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

if(!isset($_GET["match_id"]))
{
    $th = $pdo->prepare("SELECT m.id AS match_id, u.username FROM matchs m JOIN users u ON m.user_id1=u.id WHERE m.user_id2=:user");
    $th->execute(["user" => $_SESSION["user"]["id"]]);
    $matchs = $th->fetchAll();

    $th = $pdo->prepare("SELECT m.id AS match_id, u.username FROM matchs m JOIN users u ON m.user_id2=u.id WHERE m.user_id1=:user");
    $th->execute(["user" => $_SESSION["user"]["id"]]);
    $matchs = array_merge($matchs, $th->fetchAll());
}

?>

<!doctype html>
<html lang="fr" class="h-full bg-slate-50">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ChronoMatch — Mes Matchs</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body class="min-h-full flex flex-col font-sans text-slate-800 antialiased">

  <?php require "includes/header.php" ?>

  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <?php if(!isset($_GET["match_id"])): ?>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                Mes Coups de Cœur 💖
            </h1>
            <p class="text-slate-500 text-sm sm:text-base mt-1">
                Retrouvez toutes les personnes avec qui le feeling est mutuel.
            </p>
            </div>

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-100 text-rose-700 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <?= count($matchs) ?> match(s) mutuel(s)
            </div>
        </div>

        <?php if (empty($matchs)): ?>

            <!-- Aucun match pour l'instant -->
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-xs space-y-4 max-w-lg mx-auto">
                <div class="inline-flex p-4 rounded-full bg-rose-50 text-rose-500">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h2 class="text-lg font-bold text-slate-900">Pas encore de match</h2>
                    <p class="text-sm text-slate-500">Inscrivez-vous à une prochaine session pour faire vos premières rencontres !</p>
                </div>
                <a 
                    href="sessions.php" 
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-colors shadow-sm"
                >
                    Voir les sessions disponibles
                </a>
            </div>

        <?php else: ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($matchs as $match): ?>

                    <?php $matchId = encryptId($match["match_id"]) ?>

                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between gap-6">
                    
                        <div class="flex items-center gap-4">
                            <!-- Avatar avec la première lettre du prénom -->
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-pink-500 text-white font-extrabold text-xl flex items-center justify-center shadow-md shrink-0">
                                <?= strtoupper(mb_substr($match["username"], 0, 1)) ?>
                            </div>

                            <div class="space-y-1 overflow-hidden">
                                <h3 class="font-bold text-slate-900 text-lg truncate">
                                    <?= htmlspecialchars($match["username"]) ?>
                                </h3>
                                <p class="text-xs text-emerald-600 font-medium">Coup de cœur mutuel ✨</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center gap-2">
                            <a 
                            href="matchs.php?match_id=<?= $matchId ?>" 
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition-all duration-200 shadow-xs"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>Discuter</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>

    <?php else: ?>
        <?php
            $matchId = decryptId($_GET["match_id"]);
            
            $th = $pdo->prepare("SELECT m.content, m.createdAt, u.username, u.id FROM messages m JOIN users u ON m.sender=u.id WHERE m.match_id=:match_id ORDER BY m.id ASC");
            $th->execute(["match_id" => $matchId]);
            $messages = $th->fetchAll();

            $th = $pdo->prepare("SELECT user_id1 AS contact_id FROM matchs WHERE user_id2=:current_user AND id=:match_id");
            $th->execute(["current_user" => $_SESSION["user"]["id"], "match_id" => $matchId]);

            $res = $th->fetch();

            $contactId = "";
            
            if($res)
            {
                $contactId = encryptId($res["contact_id"]);
            }

            else
            {
                $th = $pdo->prepare("SELECT user_id2 AS contact_id FROM matchs WHERE user_id1=:current_user AND id=:match_id");
                $th->execute(["current_user" => $_SESSION["user"]["id"], "match_id" => $matchId]);
                $res = $th->fetch();
                $contactId = encryptId($res["contact_id"]);
            }

            $th = $pdo->prepare("SELECT username FROM users WHERE id=:contact_id");
            $th->execute(["contact_id" => $res["contact_id"]]);
            $otherUser = $th->fetch();
        ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col flex-1 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white z-10">
                <div class="flex items-center gap-3">
                    <a href="matchs.php" class="p-2 -ml-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors" title="Retour aux matchs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <!-- Avatar & Prénom -->
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-pink-500 text-white font-bold flex items-center justify-center shrink-0">
                        <?= strtoupper(mb_substr($otherUser['username'] ?? 'M', 0, 1)) ?>
                    </div>

                    <div>
                        <h1 class="font-bold text-slate-900 leading-tight">
                            <?= htmlspecialchars($otherUser['username'] ?? "Votre Match") ?>
                        </h1>
                    </div>
                </div>
            </div>

            <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50">
                <ul id="messages" class="space-y-4 list-none p-0 m-0">

                    <?php foreach($messages as $message): ?>
                        <?php
                            $date = new DateTime($message["createdAt"]);
                            //$date->setTimezone(new DateTimeZone("Europe/Paris"));

                            $isMe = ($message["user_id"] ?? null) == $_SESSION["user"]["id"] || $message["username"] === $_SESSION["user"]["username"];
                        ?>
                    
                        <li class="flex flex-col <?= $isMe ? 'items-end' : 'items-start' ?>">
                            <?php if (!$isMe): ?>
                                <span class="text-xs text-slate-400 mb-1 ml-1 font-medium">
                                    <?= htmlspecialchars($message["username"]) ?>
                                </span>
                            <?php endif; ?>

                            <div class="max-w-[80%] sm:max-w-[70%] rounded-2xl px-4 py-2.5 shadow-xs text-sm <?= $isMe ? 'bg-indigo-600 text-white rounded-br-xs' : 'bg-white text-slate-800 border border-slate-200/80 rounded-bl-xs' ?>">
                                <p class="break-words leading-relaxed"><?= htmlspecialchars($message["content"]) ?></p>
                            </div>

                            <!-- Date & Heure -->
                            <span class="text-[10px] text-slate-400 mt-1 px-1">
                                <?= $date->format("d/m/Y à H:i") ?>
                            </span>
                        </li>

                    <?php endforeach ?>

                </ul>
            </div>

            <?php $jwt = generateJWT(["id" => $_SESSION["user"]["id"]], readEnv("JWT_KEY")); ?>

            <div class="p-4 bg-white border-t border-slate-100">
                <form id="form" class="flex items-end gap-3">
                        <div class="flex-1 relative">
                            <textarea 
                                id="message" 
                                rows="1" 
                                placeholder="Écrivez votre message..." 
                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all duration-200 max-h-32"
                            ></textarea>
                        </div>

                        <button 
                            type="submit" 
                            class="inline-flex items-center justify-center p-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white transition-all duration-200 shadow-sm shrink-0"
                            title="Envoyer"
                            >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const jwtToken = <?= json_encode($jwt) ?>;
        const socket = new WebSocket(`ws://localhost:5000?token=${encodeURIComponent(jwtToken)}`);

        socket.onmessage = (res) =>
        {
            const data = JSON.parse(res.data);

            const now = new Date();
            const date = now.getDate() < 10 ? "0" + now.getDate() : now.getDate();
            const month = now.getMonth() < 10 ? "0" + now.getMonth() : now.getMonth();
            const hour = now.getHours() < 10 ? "0" + now.getHours() : now.getHours();
            const minute = now.getMinutes() < 10 ? "0" + now.getMinutes() : now.getMinutes();

            const li = document.createElement("li");
            
            li.innerHTML = `
            <li class="flex flex-col items-start">

                <span class="text-xs text-slate-400 mb-1 ml-1 font-medium">
                    ${data.username}
                </span>

                <div class="max-w-[80%] sm:max-w-[70%] rounded-2xl px-4 py-2.5 shadow-xs text-sm bg-white text-slate-800 border border-slate-200/80 rounded-bl-xs">
                    <p class="break-words leading-relaxed">${data.message}</p>
                </div>

                <!-- Date & Heure -->
                <span class="text-[10px] text-slate-400 mt-1 px-1">
                    ${date}/${month}/${now.getFullYear()} à ${hour}:${minute}
                </span>
            </li>`;
            
            document.getElementById("messages").appendChild(li);
        }

        document.getElementById("form").addEventListener("submit", async (e) =>
        {
            e.preventDefault();

            const message = document.getElementById("message").value;

            socket.send(JSON.stringify({type: "message", message, contact: "<?= $contactId ?>", username: "<?= $_SESSION["user"]["username"] ?>"}));

            document.getElementById("message").value = "";

            const now = new Date();
            const date = now.getDate() < 10 ? "0" + now.getDate() : now.getDate();
            const month = now.getMonth() < 10 ? "0" + now.getMonth() : now.getMonth();
            const hour = now.getHours() < 10 ? "0" + now.getHours() : now.getHours();
            const minute = now.getMinutes() < 10 ? "0" + now.getMinutes() : now.getMinutes();

            const li = document.createElement("li");

            li.innerHTML = `
            <li class="flex flex-col items-end">

                <div class="max-w-[80%] sm:max-w-[70%] rounded-2xl px-4 py-2.5 shadow-xs text-sm bg-indigo-600 text-white rounded-br-xs">
                    <p class="break-words leading-relaxed">${message}</p>
                </div>

                <!-- Date & Heure -->
                <span class="text-[10px] text-slate-400 mt-1 px-1">
                    ${date}/${month}/${now.getFullYear()} à ${hour}:${minute}
                </span>
            </li>`;

            document.getElementById("messages").appendChild(li);

            //////////////////////////////////////

            await fetch("api/message.php", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": '<?= $_SESSION["csrf_token"] ?>', "Content-Type": "application/json" },
                body: JSON.stringify({
                    receiver: "<?= $contactId ?>",
                    content: message,
                    matchId: "<?= $_GET["match_id"] ?>"
                })
            });
        });
    </script>

    <?php endif ?>

  </body>

  <?php require_once "includes/footer.php"; ?>
</html>