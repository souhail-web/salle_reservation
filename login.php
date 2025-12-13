<?php
session_start();

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit();
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation simple
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        // Connexion à la base de données
        require_once 'config/db.php';
        
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($user = $stmt->fetch()) {
                if (password_verify($password, $user['password'])) {
                    // Authentification réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirection vers le tableau de bord approprié
                    header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
                    exit();
                }
            }
            
            // Si on arrive ici, l'authentification a échoué
            $error = 'Identifiants invalides';
            
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Réservation de Salles</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background:
                radial-gradient(1200px 600px at 15% 10%, rgba(255, 255, 255, 0.22), transparent 60%),
                radial-gradient(900px 500px at 85% 25%, rgba(255, 255, 255, 0.16), transparent 55%),
                linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            width: 520px;
            height: 520px;
            left: -180px;
            top: -180px;
            background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.55), rgba(59, 130, 246, 0) 70%);
            filter: blur(12px);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            width: 560px;
            height: 560px;
            right: -220px;
            bottom: -220px;
            background: radial-gradient(circle at 70% 70%, rgba(168, 85, 247, 0.5), rgba(168, 85, 247, 0) 70%);
            filter: blur(14px);
            pointer-events: none;
            z-index: 0;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 18px 55px rgba(0, 0, 0, 0.14);
            transition: transform 0.3s ease;
            width: 100%;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .input-field {
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .error-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-xl fade-in relative" style="z-index: 1;">
        <div class="login-card p-6 sm:p-7">
            <!-- Logo et titre -->
            <div class="text-center mb-5">
                <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg icon-pulse">
                    <i class="fas fa-calendar-alt text-white text-xl"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-1">Réservation de Salles</h1>
                <p class="text-gray-600 text-sm sm:text-base">Connectez-vous pour gérer vos réservations</p>
            </div>

            <!-- Message d'erreur -->
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg error-shake">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulaire de connexion -->
            <form method="POST" class="space-y-4">
                <!-- Nom d'utilisateur -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-2 text-blue-500"></i>Nom d'utilisateur
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           class="w-full px-4 py-2.5 input-field rounded-lg focus:outline-none"
                           placeholder="Entrez votre nom d'utilisateur">
                </div>

                <!-- Mot de passe -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-lock mr-2 text-blue-500"></i>Mot de passe
                        </label>
                        <a href="forgot-password.php" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                            Mot de passe oublié ?
                        </a>
                    </div>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               class="w-full px-4 py-2.5 input-field rounded-lg focus:outline-none pr-10"
                               placeholder="Entrez votre mot de passe">
                        <button type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Case à cocher "Se souvenir" -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 text-sm text-gray-700">
                        Se souvenir de moi
                    </label>
                </div>

                <!-- Bouton de connexion -->
                <button type="submit" 
                        class="w-full py-2.5 px-4 btn-primary text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                </button>
            </form>

            <!-- Séparateur -->
            <div class="my-4 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Pas encore de compte ?</span>
                </div>
            </div>

            <!-- Lien vers l'inscription -->
            <div class="text-center">
                <a href="register.php" 
                   class="inline-flex items-center justify-center w-full py-2.5 px-4 border-2 border-blue-500 text-blue-600 font-medium rounded-lg hover:bg-blue-50 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Créer un compte
                </a>
            </div>

            <!-- Retour à l'accueil -->
            <div class="mt-4 text-center">
                <a href="index.php" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
                </a>
            </div>

            <!-- Informations de contact -->
            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-phone mr-1"></i> Besoin d'aide ? Contactez-nous au 01 23 45 67 89
                </p>
            </div>
        </div>
    </div>

    <script>
        // Afficher/masquer le mot de passe
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            form.style.opacity = '0';
            form.style.transform = 'translateY(6px)';
            form.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                form.style.opacity = '1';
                form.style.transform = 'translateY(0)';
            }, 60);
        });
    </script>
</body>
</html>