<?php

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

require_once "config/db.php";

$nextSession = getNextSession();
$registered = isRegistred($pdo);

$nbMatchs = getNbMatchs($pdo);
$nbDatings = getNbDatings($pdo);

?>

<!doctype html>
<html lang="fr" class="h-full bg-slate-50">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tableau de bord — Accueil</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body class="min-h-full flex flex-col font-sans text-slate-800 antialiased">

  <?php require "includes/header.php" ?>

  <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <section class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-pink-600 rounded-3xl p-6 sm:p-10 text-white shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-3">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold uppercase tracking-wider text-white">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            En ligne
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ravi de vous revoir, <?= htmlspecialchars($_SESSION["user"]["username"]) ?> !</h1>
          <p class="text-indigo-100 text-sm sm:text-base max-w-xl">
            Prêt pour de nouvelles rencontres chronométrées ? Découvrez les sessions à venir ou consultez vos matchs récents.
          </p>
        </div>

        <div class="shrink-0">
          <a 
            href="sessions.php" 
            class="inline-flex items-center justify-center px-6 py-3.5 rounded-2xl bg-white text-indigo-700 font-bold shadow-lg hover:bg-indigo-50 active:scale-95 transition-all duration-200"
          >
            Trouver une session
          </a>
        </div>
      </section>

      <!-- Cartes d'activité / Compteurs -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        
        <!-- Carte 1 : Prochaine Session -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Prochain RDV</span>
            <div class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-xl font-bold text-slate-900"><?= $nextSession ?></p>
          <p class="text-xs text-emerald-600 font-medium"><?php echo $registered ? "Inscrit(e)" : "Non inscrit(e)" ?> • 3 min par rencontre</p>
        </div>

        <!-- Carte 2 : Mes Matchs -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Coup de cœur</span>
            <div class="p-2.5 rounded-xl bg-rose-50 text-rose-500">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900"><?= $nbMatchs ?> Matchs</p>
          <a href="matchs.php" class="text-xs text-indigo-600 hover:underline font-medium inline-block">Voir les profils réciproques →</a>
        </div>

        <!-- Carte 3 : Rencontres effectuées -->
        <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Speed Dats</span>
            <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900"><?= $nbDatings ?></p>
          <p class="text-xs text-slate-500">Personnes croisée<?php if($nbDatings > 1) echo "s" ?> sur le site</p>
        </div>

      </section>

  </main>

  <?php require "includes/footer.php" ?>

  </body>
</html>