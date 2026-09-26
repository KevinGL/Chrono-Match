<?php require_once "config/db.php"; ?>

<?php

session_start();

if(!isset($_SESSION["user"]))
{
    header("location: login.php");
    exit();
}

$sessions = getNextSessions();

?>

<!doctype html>
<html lang="fr" class="h-full bg-slate-50">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ChronoMatch — Sessions à venir</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  </head>
  <body class="min-h-full flex flex-col font-sans text-slate-800 antialiased">

    <?php require "includes/header.php" ?>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Sessions de Speed Dating ⚡
                </h1>
                <p class="text-slate-500 text-sm sm:text-base mt-1">
                    Réservez votre créneau pour participer aux prochaines Rencontres Express.
                </p>
            </div>
        </div>

        <?php if(isset($_SESSION["flash"])): ?>
            <?php 
                $isError = ($_SESSION["flash"]['type'] ?? 'error') === 'error'; 
                $bgColor = $isError ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700';
            ?>
            
            <div class="p-4 rounded-2xl border text-sm font-medium flex items-center gap-3 shadow-xs <?= $bgColor ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($isError): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    <?php endif; ?>
                </svg>
                <span><?= htmlspecialchars($_SESSION["flash"]["content"]) ?></span>
            </div>

        <?php unset($_SESSION["flash"]) ?>
        <?php endif ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($sessions as $s): ?>
                <?php 
                    $timestamp = $s->getTimestamp();
                    $isRegistered = registered($pdo, $s);
                ?>

                <div class="bg-white rounded-2xl border transition-all duration-200 shadow-xs hover:shadow-md p-6 flex flex-col justify-between gap-6 <?= $isRegistered ? 'border-indigo-300 ring-2 ring-indigo-500/10' : 'border-slate-200/80' ?>">
                
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $isRegistered ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $isRegistered ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                            <?= $isRegistered ? 'Inscrit(e)' : 'Disponible' ?>
                        </span>
                        
                        <span class="text-xs text-slate-400 font-mono">
                            #<?= substr(md5($timestamp), 0, 6) ?>
                        </span>

                        </div>
                
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-indigo-600 font-bold text-lg">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span><?= ucfirst($s->format("d-m-Y")) ?></span>
                            </div>

                            <div class="flex items-center gap-2 text-slate-500 text-sm pl-7">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>À <?= $s->format("H:i") ?> (Durée : ~1h)</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <?php if (!$isRegistered): ?>
                        <a 
                            href="register.php?session=<?= $timestamp ?>" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 shadow-sm transition-all duration-200"
                        >
                        <span>S'inscrire à la session</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                        </a>

                        <?php else: ?>
                        <a 
                            href="unregister.php?session=<?= $timestamp ?>"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-medium text-sm text-rose-600 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 border border-rose-200 transition-all duration-200"
                        >
                            <span>Se désinscrire</span>
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach ?>
        </div>

    </main>

    <?php require "includes/footer.php" ?>

    </body>
</html>