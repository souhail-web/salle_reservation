<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
redirectIfNotLoggedIn();
if (!isAdmin()) {
    header('Location: ../user/dashboard.php');
    exit();
}

$message = '';
$error = '';

// Delete user
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Vous ne pouvez pas supprimer votre propre compte.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
            $stmt->execute([$user_id]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Utilisateur supprimé avec succès.';
            } else {
                $error = 'Impossible de supprimer cet utilisateur.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la suppression de l\'utilisateur.';
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY username");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des utilisateurs.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Réservation de Salles</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rooms.php"><i class="fas fa-door-open"></i> Gestion des salles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php"><i class="fas fa-users"></i> Utilisateurs</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</span>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-users"></i> Gestion des utilisateurs</h2>
        <p class="lead">Consultez et gérez les utilisateurs du système.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> Liste des utilisateurs</span>
                <span class="badge bg-primary"><?php echo count($users); ?> utilisateurs</span>
            </div>
            <div class="card-body">
                <?php if (isset($users) && count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom d'utilisateur</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo formatDateTime($user['created_at']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucun utilisateur trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>