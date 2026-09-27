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

<!doctype html>
<html lang="fr" class="h-full bg-slate-50">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>ChronoMatch — Speed dating</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="h-full flex flex-col font-sans antialiased selection:bg-rose-500 selection:text-white">

        <?php require_once "includes/header.php" ?>

        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <div class="mt-4 bg-slate-800/80 backdrop-blur border border-slate-700/60 rounded-2xl p-4 shadow-xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div>
                        <p id="status" class="text-xs text-slate-400"></p>
                    </div>
                </div>

                <!-- Chronomètre 180s -->
                <div class="flex items-center gap-2 bg-rose-500/10 border border-rose-500/20 px-3.5 py-1.5 rounded-full text-rose-400 font-mono font-bold text-sm sm:text-base">
                    <i class="fa-solid fa-clock animate-pulse"></i>
                    <span id="timer" hidden>03:00</span>
                </div>
            </div>

            <div id="chat" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm flex flex-col flex-1 overflow-hidden" hidden>

                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white z-10">
                    <div class="flex items-center gap-3">
                        <a href="sessions.php" class="p-2 -ml-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors" title="Retour aux sessions">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>

                        <!-- Avatar & Prénom -->
                        <div id="avatar" class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-pink-500 text-white font-bold flex items-center justify-center shrink-0"></div>

                        <div>
                            <h1 id="other_contact" class="font-bold text-slate-900 leading-tight"></h1>
                        </div>
                    </div>
                </div>

                <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50">
                    <ul id="messages" class="space-y-4 list-none p-0 m-0">
                    </ul>
                </div>

                
                <div class="p-4 bg-white border-t border-slate-100">
                    <form id="form" class="flex items-end gap-3">
                            <div class="flex-1 relative">
                                <textarea id="message" rows="1" placeholder="Écrivez votre message..." class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all duration-200 max-h-32"></textarea>
                            </div>

                            <button type="submit" class="inline-flex items-center justify-center p-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white transition-all duration-200 shadow-sm shrink-0" title="Envoyer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                    </form>
                </div>
            </div>

            <div id="modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-50" hidden>
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center shadow-2xl space-y-6">
                    
                    <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner">
                        <i class="fa-solid fa-heart"></i>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-xl font-bold text-slate-100">Temps écoulé !</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            Avez-vous eu un bon feeling durant ces 3 minutes ?
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button 
                            id="no" 
                            type="button" 
                            class="w-full py-3 px-4 rounded-xl border border-slate-700 bg-slate-800/60 hover:bg-slate-800 text-slate-300 font-semibold text-sm transition-all active:scale-95 flex items-center justify-center gap-2"
                        >
                            <i class="fa-solid fa-xmark text-slate-400"></i> Non
                        </button>
                        
                        <button 
                            id="yes" 
                            type="button" 
                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-semibold text-sm shadow-lg shadow-rose-500/25 transition-all active:scale-95 flex items-center justify-center gap-2"
                        >
                            <i class="fa-solid fa-heart"></i> Oui !
                        </button>
                    </div>

                </div>
            </div>
        </main>

        <?php require_once "includes/footer.php" ?>

        <script>
            const jwtToken = <?= json_encode($jwt) ?>;
            const socket = new WebSocket(`ws://localhost:4000?token=${encodeURIComponent(jwtToken)}`);
            let idContact = "";
            let endChrono = 0;

            socket.onmessage = (res) =>
            {
                const data = JSON.parse(res.data);

                const now = new Date();
                const date = now.getDate() < 10 ? "0" + now.getDate() : now.getDate();
                const month = now.getMonth() < 10 ? "0" + now.getMonth() : now.getMonth();
                const hour = now.getHours() < 10 ? "0" + now.getHours() : now.getHours();
                const minute = now.getMinutes() < 10 ? "0" + now.getMinutes() : now.getMinutes();

                if(data.status === "waiting")
                {
                    document.getElementById("status").innerText = "Patientez nous vous mettons en relation avec quelqu'un ...";
                }

                else
                if(data.status === "contact")
                {
                    document.getElementById("status").innerText = `Voici ${data.contact.username}`;
                    document.getElementById("chat").hidden = false;
                    document.getElementById("timer").hidden = false;
                    document.getElementById("avatar").innerText = `${data.contact.username.charAt(0).toUpperCase()}`;
                    document.getElementById("other_contact").innerText = `${data.contact.username}`;
                    
                    idContact = data.contact.id;

                    setInterval(() =>
                    {
                        const now = Date.now();
                        const diffInSeconds = Math.max(0, Math.floor((data.expiresAt - now) / 1000));

                        const minutes = String(Math.floor(diffInSeconds / 60)).padStart(2, '0');
                        const seconds = String(diffInSeconds % 60).padStart(2, '0');

                        document.getElementById("timer").textContent = `${minutes}:${seconds}`;
                    }, 1000);

                    const response = fetch(`api/dating.php?contact=${idContact}`, {headers: { "X-CSRF-TOKEN": '<?= $_SESSION["csrf_token"] ?>' }});
                
                    if (!response.ok)
                    {
                        throw new Error(`Erreur HTTP : ${response.status}`);
                    }
                }

                else
                if(data.status === "receive")
                {
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

                else
                if(data.status === "disconnect")
                {
                    document.getElementById("chat").hidden = true;
                    document.getElementById("messages").hidden = true;
                    document.getElementById("modal").hidden = false;
                    document.getElementById("timer").hidden = true;

                    document.getElementById("yes").addEventListener("click", async () =>
                    {
                        document.getElementById("modal").hidden = true;
                        document.getElementById("status").innerText = "Patientez nous vous mettons en relation avec quelqu'un ...";

                        const response = await fetch(`api/like.php?contact=${data.contact}`, {headers: { "X-CSRF-TOKEN": '<?= $_SESSION["csrf_token"] ?>' }});
                
                        if (!response.ok)
                        {
                            throw new Error(`Erreur HTTP : ${response.status}`);
                        }
                    });

                    document.getElementById("no").addEventListener("click", () =>
                    {
                        document.getElementById("modal").hidden = true;
                        document.getElementById("status").innerText = "Patientez nous vous mettons en relation avec quelqu'un ...";
                    });
                }
            }

            document.getElementById("form").addEventListener("submit", async (e) =>
            {
                e.preventDefault();
                
                const message = document.getElementById("message").value;

                socket.send(JSON.stringify({type: "message", message}));

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
                        receiver: idContact,
                        content: message,
                        createAt: Date.now()
                    })
                });
            });
        </script>
    </body>
</html>