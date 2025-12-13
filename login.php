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
    <title>Connexion - Système de Réservation de Salles</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('assets/images/BCN TECH_ Mesa de juntas.jfif') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            background-attachment: fixed;
        }
        .login-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .input-field {
            background-color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
            border-color: #3b82f6;
        }
        .btn-login {
            background: linear-gradient(45deg, #3b82f6, #6366f1);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="login-container rounded-2xl overflow-hidden">
            <div class="p-8 bg-white bg-opacity-90 backdrop-blur-sm">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-calendar-alt text-white text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Connexion</h1>
                    <p class="text-gray-600">Accédez à votre espace de réservation</p>
                </div>

                <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   required 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   class="input-field block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg shadow-sm focus:outline-none sm:text-sm"
                                   placeholder="Votre nom d'utilisateur">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                            <a href="forgot-password.php" class="text-xs text-blue-600 hover:text-blue-500">Mot de passe oublié ?</a>
                        </div>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="input-field block w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg shadow-sm focus:outline-none sm:text-sm"
                                   placeholder="Votre mot de passe">
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500 focus:outline-none toggle-password">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" 
                                   name="remember" 
                                   type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Se souvenir de moi
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="btn-login w-full flex justify-center py-3 px-4 border border-transparent rounded-lg text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i> Se connecter
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-sm text-gray-600">
                        Vous n'avez pas de compte ? 
                        <a href="register.php" class="font-semibold text-blue-600 hover:text-blue-500 hover:underline transition-colors">
                            Créer un compte
                        </a>
                    </p>
                    <div class="mt-4 flex justify-center space-x-4">
                        <a href="#" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                            <i class="fab fa-google text-red-500"></i>
                        </a>
                        <a href="#" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                            <i class="fab fa-microsoft text-blue-600"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Afficher/masquer le mot de passe avec animation
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const passwordInput = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                // Animation de l'icône
                icon.style.transition = 'transform 0.3s ease';
                icon.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                    icon.style.transform = 'scale(1.1)';
                    
                    setTimeout(() => {
                        icon.style.transform = 'scale(1)';
                    }, 150);
                }, 100);
            });
        });

        // Animation au chargement de la page
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            if (form) {
                form.style.opacity = '0';
                form.style.transform = 'translateY(20px)';
                form.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                
                setTimeout(() => {
                    form.style.opacity = '1';
                    form.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>