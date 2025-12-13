<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
redirectIfNotLoggedIn();

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

// Fetch user reservations
try {
    $stmt = $pdo->prepare("SELECT r.*, ro.name as room_name FROM reservations r 
                          JOIN rooms ro ON r.room_id = ro.id 
                          WHERE r.user_id = ? 
                          ORDER BY r.reservation_date DESC, r.start_time DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement de vos réservations.";
}

// Fetch all rooms for reservation form
try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des salles.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Réservation de Salles</title>
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
                        <a class="nav-link" href="reserve.php"><i class="fas fa-calendar-plus"></i> Réserver une salle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php"><i class="fas fa-calendar-alt"></i> Planning</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-user"></i> Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p class="lead">Voici votre tableau de bord personnel pour la gestion de vos réservations.</p>
        
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
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-history"></i> Vos réservations</span>
                        <a href="reserve.php" class="btn btn-sm btn-primary">Nouvelle réservation</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($reservations) && count($reservations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Salle</th>
                                        <th>Date</th>
                                        <th>Heures</th>
                                        <th>Objectif</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                                        <td><?php echo formatDate($reservation['reservation_date']); ?></td>
                                        <td><?php echo substr($reservation['start_time'], 0, 5); ?> - <?php echo substr($reservation['end_time'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                                        <td>
                                            <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                            <a href="cancel_reservation.php?id=<?php echo $reservation['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                <i class="fas fa-times"></i> Annuler
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted">Vous n'avez aucune réservation pour le moment. 
                           <a href="reserve.php">Réservez une salle maintenant</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-door-open"></i> Salles disponibles
                    </div>
                    <div class="card-body">
                        <?php if (isset($rooms) && count($rooms) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($rooms as $room): ?>
                            <li class="list-group-item">
                                <h6><?php echo htmlspecialchars($room['name']); ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-users"></i> <?php echo $room['capacity']; ?> personnes<br>
                                    <i class="fas fa-tools"></i> <?php echo htmlspecialchars($room['equipment']); ?>
                                </small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-muted">Aucune salle disponible pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>