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

<!doctype html>
<html lang="fr" class="h-full bg-slate-50">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion — ChronoMatch</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body class="min-h-full flex items-center justify-center p-4 sm:p-6 lg:p-12 text-slate-800">

    <div class="w-full max-w-lg md:max-w-xl bg-white rounded-2xl p-6 sm:p-10 shadow-xl border border-slate-200/80 space-y-8">

    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 mb-2 border border-indigo-100">
          <!-- Icône Cadenas -->
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Bienvenue</h1>
        <p class="text-sm sm:text-base text-slate-500">Saisissez vos identifiants pour accéder à votre espace</p>
      </div>

      <form method="POST" action="includes/auth.php" class="space-y-6">

        <div class="space-y-2">
          <label for="email" class="block text-sm font-medium text-slate-700">
            Adresse email
          </label>

          <input type="email" name="email" id="email" placeholder="nom@exemple.com" required class="w-full px-4 py-3.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-base focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 transition-all duration-200" />
        </div>

          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700">
              Mot de passe
            </label>

            <input type="password" id="password" placeholder="••••••••" name="password" required class="w-full px-4 py-3.5 rounded-xl bg-slate-50 border border-slate-300 text-slate-900 placeholder-slate-400 text-base focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-600 transition-all duration-200" />
          </div>

          <input type="hidden" name="csrf" value="<?= $token ?>" />

          <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-semibold text-base text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-md shadow-indigo-600/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer">Se connecter<button>

      </form>
    
    </div>

  </body>
</html>