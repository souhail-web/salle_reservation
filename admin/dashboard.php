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

// Handle session messages
$message = '';
$error = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch statistics
try {
    // Total rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
    $total_rooms = $stmt->fetch()['total'];
    
    // Total reservations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
    $total_reservations = $stmt->fetch()['total'];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $total_users = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des statistiques.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - Réservation de Salles</title>
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
                        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rooms.php"><i class="fas fa-door-open"></i> Gestion des salles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users"></i> Utilisateurs</a>
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
        <h2><i class="fas fa-tachometer-alt"></i> Tableau de bord administrateur</h2>
        <p class="lead">Bienvenue dans le panneau d'administration du système de réservation de salles.</p>
        
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
        
        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header"><i class="fas fa-door-open"></i> Salles</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_rooms; ?> salles</h5>
                        <p class="card-text">Nombre total de salles disponibles dans le système.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header"><i class="fas fa-calendar-check"></i> Réservations</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_reservations; ?> réservations</h5>
                        <p class="card-text">Nombre total de réservations effectuées.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header"><i class="fas fa-users"></i> Utilisateurs</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_users; ?> utilisateurs</h5>
                        <p class="card-text">Nombre d'utilisateurs inscrits (hors administrateurs).</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Reservations -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Réservations récentes
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT r.*, u.username, ro.name as room_name 
                                                FROM reservations r 
                                                JOIN users u ON r.user_id = u.id 
                                                JOIN rooms ro ON r.room_id = ro.id 
                                                ORDER BY r.created_at DESC 
                                                LIMIT 5");
                            $reservations = $stmt->fetchAll();
                            
                            if (count($reservations) > 0):
                        ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Salle</th>
                                        <th>Date</th>
                                        <th>Heures</th>
                                        <th>Objectif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                                        <td><?php echo formatDate($reservation['reservation_date']); ?></td>
                                        <td><?php echo substr($reservation['start_time'], 0, 5); ?> - <?php echo substr($reservation['end_time'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Aucune réservation pour le moment.</p>
                        <?php endif; ?>
                        <?php } catch (PDOException $e) { ?>
                        <p class="text-danger">Erreur lors du chargement des réservations récentes.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>