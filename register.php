<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Ce nom d\'utilisateur ou cette adresse email est déjà utilisé(e).';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création du compte.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Réservation de Salles</title>
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
        
        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 18px 55px rgba(0, 0, 0, 0.14);
            transition: transform 0.3s ease;
            width: 100%;
        }
        
        .register-card:hover {
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
            padding: 0.75rem 1rem 0.75rem 3rem;
            width: 100%;
            border-radius: 0.5rem;
            font-size: 0.9375rem;
        }
        
        .input-field:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
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

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.2s;
        }

        .input-field:focus + .input-icon {
            color: #667eea;
        }
    </style>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-xl fade-in relative" style="z-index: 1;">
        <div class="register-card p-6 sm:p-8">
            <!-- Logo et titre -->
            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg icon-pulse">
                    <i class="fas fa-user-plus text-white text-xl"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-1">Créer un compte</h1>
                <p class="text-gray-600 text-sm sm:text-base">Rejoignez notre plateforme de réservation</p>
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

            <!-- Message de succès -->
            <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">Nom d'utilisateur</label>
                    <div class="relative">
                        <input type="text" name="username" id="username" class="input-field" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="ex: JohnDoe" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email</label>
                    <div class="relative">
                        <input type="email" name="email" id="email" class="input-field" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="ex: exemple@domaine.com" required>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Mot de passe</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="input-field pr-12" placeholder="••••••" required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimum 6 caractères</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1.5">Confirmer le mot de passe</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" class="input-field pr-12" placeholder="••••••" required>
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none toggle-confirm-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-medium text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-user-plus mr-2"></i> Créer un compte
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm">
                <p class="text-gray-600">
                    Vous avez déjà un compte ? 
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i> Se connecter
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Afficher/masquer le mot de passe
        document.querySelectorAll('.toggle-password, .toggle-confirm-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Animation du formulaire au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            if (form) {
                form.classList.add('fade-in');
            }
        });
    </script>
</body>
</html>