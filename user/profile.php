<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Mon profil';

require_once '../config/db.php';
require_once '../includes/functions.php';

redirectIfNotLoggedIn();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $new_password = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '') {
        $error = 'Veuillez remplir le nom d\'utilisateur et l\'email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez saisir un email valide.';
    } elseif ($new_password !== '' && strlen($new_password) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif ($new_password !== '' && $new_password !== $confirm_password) {
        $error = 'La confirmation du mot de passe ne correspond pas.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
            $stmt->execute([$username, $email, (int)$user['id']]);
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                $error = 'Ce nom d\'utilisateur ou cet email est déjà utilisé.';
            } else {
                $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
                $stmt->execute([$username, $email, (int)$user['id']]);

                if ($new_password !== '') {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$hashed, (int)$user['id']]);
                }

                $_SESSION['username'] = $username;

                $stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
                $stmt->execute([(int)$user['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                $success = 'Profil mis à jour avec succès.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour du profil.';
        }
    }
}

require_once '../includes/header.php';
?>

<section class="py-10 md:py-14">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">Mon profil</h1>
                <p class="text-gray-600 mt-1">Gérez vos informations personnelles.</p>
            </div>
            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-900 font-semibold hover:bg-gray-50 transition">
                Retour au dashboard
            </a>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 text-green-700">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xl">
                            <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Rôle</span>
                            <span class="text-sm font-semibold text-gray-900"><?php echo ($user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Créé le</span>
                            <span class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars(date('d/m/Y', strtotime((string)$user['created_at']))); ?></span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Modifier mes informations</h2>

                    <form method="post" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="username">Nom d'utilisateur</label>
                                <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="email">Email</label>
                                <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5">
                            <h3 class="text-base font-bold text-gray-900 mb-3">Changer le mot de passe (optionnel)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2" for="new_password">Nouveau mot de passe</label>
                                    <input id="new_password" name="new_password" type="password" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="new-password">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2" for="confirm_password">Confirmer</label>
                                    <input id="confirm_password" name="confirm_password" type="password" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 justify-end pt-2">
                            <a href="dashboard.php" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-gray-900 font-semibold border border-gray-200 hover:bg-gray-50 transition">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
