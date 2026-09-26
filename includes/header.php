<?php
// Détection du fichier actuel pour mettre en valeur le lien actif
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<header class="bg-white border-b border-slate-200/80 sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      
      <!-- Logo / Nom du projet -->
      <a href="home.php" class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-rose-500 flex items-center justify-center text-white font-black text-base shadow-sm">
          ⚡
        </span>
        <span class="font-extrabold text-lg text-slate-900 tracking-tight">
          Chrono<span class="text-indigo-600">Match</span>
        </span>
      </a>

      <!-- Liens Desktop -->
      <nav class="hidden md:flex items-center gap-1">
        <?php
          $links = [
            'home.php' => 'Accueil',
            'sessions.php' => 'Prochaines sessions',
            'matchs.php' => 'Matchs',
          ];

          foreach ($links as $url => $label):
            $isActive = ($currentScript === $url);
            $class = $isActive 
              ? 'bg-indigo-50 text-indigo-600 font-bold' 
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium';
        ?>
          <a href="<?= $url ?>" class="px-4 py-2 rounded-xl text-sm transition-colors duration-150 <?= $class ?>">
            <?= $label ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <!-- Bouton Déconnexion (Desktop) -->
      <div class="hidden md:flex items-center gap-3">
        <?php if (isset($_SESSION['user'])): ?>
          <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-slate-100 text-slate-600">
            👤 <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Mon Profil') ?>
          </span>
          <a href="logout.php" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Déconnexion
          </a>
        <?php endif; ?>
      </div>

      <!-- Bouton Burger Mobile -->
      <div class="md:hidden flex items-center">
        <button id="mobile-menu-btn" type="button" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

    </div>
  </div>

  <!-- Menu Mobile (masqué par défaut) -->
  <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 bg-white px-4 pt-2 pb-4 space-y-1">
    <a href="home.php" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Accueil</a>
    <a href="sessions.php" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Prochaines sessions</a>
    <a href="matches.php" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:bg-slate-50">Matchs</a>
    <div class="pt-2 border-t border-slate-100">
      <a href="logout.php" class="block px-3 py-2 rounded-lg text-base font-medium text-rose-600 hover:bg-rose-50">Déconnexion</a>
    </div>
  </div>
</header>

<script>
  // Toggle du menu mobile
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
  }
</script>