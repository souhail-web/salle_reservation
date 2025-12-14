<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Mon profil';

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user = null;
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = 'Utilisateur introuvable.';
    }
} catch (PDOException $e) {
    $error = 'Erreur lors du chargement de votre profil.';
}

// Mettre à jour les informations de base (nom d'utilisateur et email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $error = '';
    $success = '';

    try {
        // Validation des champs
        if (empty($username) || empty($email)) {
            $error = 'Tous les champs sont obligatoires.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer une adresse email valide.';
        } else {
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                // Mettre à jour les informations de base
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $_SESSION['user_id']])) {
                    $success = 'Vos informations ont été mises à jour avec succès.';
                    // Rafraîchir les données de l'utilisateur
                    $stmt = $pdo->prepare('SELECT id, username, email, created_at FROM users WHERE id = ?');
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Mettre à jour les informations dans la session
                    $_SESSION['username'] = $username;
                } else {
                    $error = 'Une erreur est survenue lors de la mise à jour de votre profil.';
                }
            }
        }
    } catch (PDOException $e) {
        $error = 'Une erreur est survenue lors de la mise à jour de votre profil.';
        error_log('Erreur de mise à jour du profil: ' . $e->getMessage());
    }
}

// Mettre à jour le mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $error = '';
    $success = '';

    try {
        // Vérifier le mot de passe actuel
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($current_password, $user_data['password'])) {
            $error = 'Le mot de passe actuel est incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            // Mettre à jour le mot de passe
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                $success = 'Votre mot de passe a été mis à jour avec succès.';
            } else {
                $error = 'Une erreur est survenue lors de la mise à jour de votre mot de passe.';
            }
        }
    } catch (PDOException $e) {
        $error = 'Une erreur est survenue lors de la mise à jour de votre mot de passe.';
        error_log('Erreur de mise à jour du mot de passe: ' . $e->getMessage());
    }
}

// Récupérer les données de l'utilisateur
if (empty($user)) {
    try {
        $stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement de votre profil.';
        error_log('Erreur de chargement du profil: ' . $e->getMessage());
    }
}

require_once '../includes/header.php';
?>


<style>
    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .animate-gradient {
        background-size: 400% 400%;
        animation: gradient 15s ease infinite;
    }
    
    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .input-focus:focus {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        border-color: #6366f1;
    }
    
    .btn-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn-hover:hover {
        transform: translateY(-2px);
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .avatar-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .avatar-hover:hover {
        transform: scale(1.05) rotate(5deg);
    }
</style>

<section class="min-h-screen py-8 sm:py-12 bg-gradient-to-br from-indigo-50 via-blue-50 to-purple-50 animate-gradient">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Messages d'alerte -->
        <div class="mb-8 space-y-4">
            <?php if ($error): ?>
                <div class="rounded-md bg-red-50 p-4 border-l-4 border-red-500">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="rounded-md bg-green-50 p-4 border-l-4 border-green-500">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($user): ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Carte de profil -->
                <div class="lg:col-span-4 h-full">
                    <div class="bg-white/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-2xl transition-all duration-500 card-hover border border-white/30 relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-blue-100/50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="px-6 py-8 sm:p-10">
                            <div class="flex flex-col items-center">
                                <div class="h-28 w-28 rounded-full bg-gradient-to-br from-indigo-600 via-blue-500 to-purple-500 flex items-center justify-center text-white text-4xl font-bold shadow-xl mb-6 transform hover:scale-110 transition-all duration-500 avatar-hover relative z-10">
                                    <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></h3>
                                <p class="text-indigo-600 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                                
                                <div class="mt-8 w-full space-y-4 relative z-10">
                                    <div class="flex items-center justify-between border-b border-gray-100/50 pb-3 group">
                                        <span class="text-sm font-medium text-gray-500">Membre depuis</span>
                                        <span class="text-sm font-medium text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-500">Statut</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Actif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 sm:px-10 border-t border-gray-100">
                            <div class="text-sm text-center">
                                <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                                    Voir l'activité récente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de modification -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white/80 backdrop-blur-lg shadow-2xl overflow-hidden rounded-2xl transition-all duration-500 card-hover border border-white/30 relative overflow-hidden group-form">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-blue-100/50 opacity-0 group-form-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-blue-600">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-indigo-100/80 backdrop-blur-sm flex items-center justify-center shadow-inner">
                                    <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xl font-bold text-white">Informations du profil</h3>
                                    <p class="text-sm text-indigo-100">Mettez à jour vos informations personnelles</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulaire pour les informations de base (nom d'utilisateur et email) -->
                        <form method="post" action="" class="mb-8">
                            <div class="px-6 py-6 space-y-8 sm:p-8">
                                <div class="flex items-center mb-6">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-indigo-50/80 backdrop-blur-sm flex items-center justify-center shadow-inner">
                                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-gray-900">Informations personnelles</h4>
                                        <p class="text-sm text-gray-500">Gérez vos informations personnelles</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="username" class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                                        <div class="mt-1 relative rounded-xl shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <input type="text" name="username" id="username" autocomplete="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 focus:outline-none block w-full pl-12 sm:text-sm border-gray-200/80 rounded-xl py-3 border transition-all duration-300 ease-in-out hover:border-indigo-300 bg-white/80 backdrop-blur-sm input-focus" placeholder="Votre nom d'utilisateur" required>
                                        </div>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3">
                                        <label for="email" class="block text-sm font-medium text-gray-700">Adresse email</label>
                                        <div class="mt-1 relative rounded-xl shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                                </svg>
                                            </div>
                                            <input type="email" name="email" id="email" autocomplete="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:outline-none block w-full pl-12 sm:text-sm border-gray-200/80 rounded-xl py-3 border transition-all duration-300 ease-in-out hover:border-blue-300 bg-white/80 backdrop-blur-sm input-focus" placeholder="votre@email.com" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-gray-100">
                                    <div class="flex justify-end">
                                        <button type="submit" name="update_profile" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl shadow-lg text-white bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl">
                                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5v2a1 1 0 102 0V6a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h5a1 1 0 100-2H4V6h5v5.586l-1.293-1.293a1 1 0 00-1.414 0z" />
                                            </svg>
                                            Mettre à jour les informations
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Section Sécurité du compte -->
                        <form method="post" action="" class="mt-8 border-t border-gray-200 pt-8">
                            <div class="px-6 py-6 space-y-8 sm:p-8">
                                <div class="flex items-center mb-6">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-amber-50/80 backdrop-blur-sm flex items-center justify-center shadow-inner">
                                        <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium text-gray-900">Sécurité du compte</h4>
                                        <p class="text-sm text-gray-500">Mettez à jour votre mot de passe</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <div class="sm:col-span-6">
                                            <label for="current_password" class="block text-sm font-medium text-gray-700">Mot de passe actuel <span class="text-red-500">*</span></label>
                                            <div class="mt-1 relative rounded-xl shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <input type="password" name="current_password" id="current_password" autocomplete="current-password" class="focus:ring-2 focus:ring-amber-400 focus:border-amber-400 focus:outline-none block w-full sm:text-sm border-gray-200/80 rounded-xl py-3 border transition-all duration-300 ease-in-out hover:border-amber-300 bg-white/90 backdrop-blur-sm input-focus pl-12" placeholder="••••••••" required>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-600 font-medium">Entrez votre mot de passe actuel pour confirmer les changements</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <div class="sm:col-span-3">
                                            <label for="new_password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                            <div class="mt-1 relative rounded-xl shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <input type="password" name="new_password" id="new_password" autocomplete="new-password" class="focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none block w-full sm:text-sm border-gray-200/80 rounded-xl py-3 border transition-all duration-300 ease-in-out hover:border-emerald-300 bg-white/90 backdrop-blur-sm input-focus pl-12" placeholder="••••••••">
                                            </div>
                                            <p class="mt-1 text-xs text-gray-600 font-medium">Minimum 6 caractères</p>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmer le nouveau mot de passe</label>
                                            <div class="mt-1 relative rounded-xl shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-cyan-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M2.5 10a.5.5 0 01.5.5v5a.5.5 0 00.5.5h14a.5.5 0 00.5-.5v-5a.5.5 0 011 0v5a1.5 1.5 0 01-1.5 1.5H3A1.5 1.5 0 011.5 15v-5a.5.5 0 01.5-.5z" clip-rule="evenodd" />
                                                        <path fill-rule="evenodd" d="M10 2a.5.5 0 01.5.5v8.793l1.146-1.147a.5.5 0 01.708.708l-2 2a.5.5 0 01-.708 0l-2-2a.5.5 0 01.708-.708L9.5 11.293V2.5A.5.5 0 0110 2z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password" class="focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 focus:outline-none block w-full sm:text-sm border-gray-200/80 rounded-xl py-3 border transition-all duration-300 ease-in-out hover:border-cyan-300 bg-white/90 backdrop-blur-sm input-focus pl-12" placeholder="••••••••">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-6">
                                        <div class="flex justify-end">
                                            <button type="submit" name="update_password" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl shadow-lg text-white bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl">
                                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                </svg>
                                                Mettre à jour le mot de passe
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
