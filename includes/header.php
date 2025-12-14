<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_subdir = isset($_SERVER['PHP_SELF']) && preg_match('~/(user|admin)/~', (string)$_SERVER['PHP_SELF']);
$prefix = $is_subdir ? '../' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation de Salles - <?php echo $pageTitle ?? 'Accueil'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
        }
        .room-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .nav-link {
            position: relative;
        }
        .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }
        .nav-link:hover:after {
            width: 100%;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du menu mobile
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const expanded = this.getAttribute('aria-expanded') === 'true' || false;
                    this.setAttribute('aria-expanded', !expanded);
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Gestion du menu utilisateur
            const userMenuButton = document.getElementById('user-menu-button');
            if (userMenuButton) {
                userMenuButton.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    menu.classList.toggle('hidden');
                });

                // Fermer le menu utilisateur en cliquant à l'extérieur
                document.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !event.target.matches('.user-menu')) {
                        const menu = userMenuButton.nextElementSibling;
                        if (menu && !menu.classList.contains('hidden')) {
                            menu.classList.add('hidden');
                        }
                    }
                });
            }

            // Fermer le menu mobile au clic sur un lien
            if (mobileMenu) {
                mobileMenu.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        if (mobileMenuButton) {
                            mobileMenuButton.setAttribute('aria-expanded', 'false');
                        }
                    });
                });
            }
        });
    </script>
</head>
<body class="bg-gray-50">
    <!-- Barre de navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo $prefix; ?>home.php" class="flex items-center">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">EspacePro</span>
                    </a>
                </div>

                <!-- Navigation principale -->
                <nav class="hidden md:ml-6 md:flex md:items-center md:space-x-8">
                    <a href="<?php echo $prefix; ?>home.php" class="px-3 py-2 text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                        Accueil
                    </a>
                    <a href="<?php echo $prefix; ?>home.php#salles" class="px-3 py-2 text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                        Nos salles
                    </a>
                    <a href="<?php echo $prefix; ?>home.php#how-it-works" class="px-3 py-2 text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                        Fonctionnement
                    </a>
                </nav>

                <!-- Actions utilisateur -->
                <div class="hidden md:ml-4 md:flex-shrink-0 md:flex md:items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="ml-3 relative">
                            <div>
                                <div class="relative group">
                                    <div class="flex items-center">
                                        <button type="button" 
                                                class="bg-white rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 group" 
                                                id="user-menu-button" 
                                                aria-expanded="false" 
                                                aria-haspopup="true">
                                            <span class="sr-only">Ouvrir le menu utilisateur</span>
                                            <div class="flex items-center">
                                                <div class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold transition-all duration-200 group-hover:bg-blue-600 group-hover:text-white">
                                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                                </div>
                                                <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors duration-200 hidden md:inline-flex items-center">
                                                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?>
                                                    <svg class="ml-1 -mr-1 h-4 w-4 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                    <!-- Menu déroulant amélioré -->
                                    <div class="absolute right-0 mt-2 w-56 origin-top-right z-50">
                                        <div id="user-menu" 
                                             class="rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-1 group-hover:translate-y-0 py-1" 
                                             role="menu" 
                                             aria-orientation="vertical" 
                                             tabindex="-1"
                                             style="min-width: 14rem;"
                                             data-dropdown-placement="bottom-end">
                                            <a href="<?php echo $prefix . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'); ?>" 
                                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150" 
                                               role="menuitem" 
                                               tabindex="-1">
                                                <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                                </svg>
                                                Tableau de bord
                                            </a>
                                            <a href="<?php echo $prefix; ?>user/profile.php" 
                                               class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150" 
                                               role="menuitem" 
                                               tabindex="-1">
                                                <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Mon profil
                                            </a>
                                            <a href="<?php echo $prefix; ?>logout.php" 
                                               class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150" 
                                               role="menuitem" 
                                               tabindex="-1">
                                                <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Déconnexion
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-4">
                            <a href="<?php echo $prefix; ?>login.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                Connexion
                            </a>
                            <a href="<?php echo $prefix; ?>register.php" class="ml-4 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                S'inscrire
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bouton menu mobile -->
                <div class="-mr-2 flex items-center md:hidden">
                    <button type="button" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="mobile-menu-button" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Ouvrir le menu principal</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu mobile -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="<?php echo $prefix; ?>home.php" class="bg-blue-50 border-blue-500 text-blue-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Accueil
                </a>
                <a href="<?php echo $prefix; ?>home.php#salles" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Nos salles
                </a>
                <a href="<?php echo $prefix; ?>home.php#how-it-works" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Fonctionnement
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $prefix . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'); ?>" class="border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        Dashboard
                    </a>
                <?php endif; ?>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold">
                                <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Utilisateur'); ?></div>
                            <div class="text-sm font-medium text-gray-500"><?php echo ($_SESSION['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'); ?></div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="<?php echo $prefix . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'); ?>" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            Tableau de bord
                        </a>
                        <a href="<?php echo $prefix; ?>user/profile.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            Mon profil
                        </a>
                        <a href="<?php echo $prefix; ?>logout.php" class="block px-4 py-2 text-base font-medium text-red-600 hover:bg-red-50">
                            Déconnexion
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-3 space-y-1">
                        <a href="<?php echo $prefix; ?>login.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            Connexion
                        </a>
                        <a href="<?php echo $prefix; ?>register.php" class="block px-4 py-2 text-base font-medium text-blue-600 hover:bg-blue-50">
                            Créer un compte
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>


    <main class="main-content">
