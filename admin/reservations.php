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

// Get filter parameters
$room_filter = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Fetch all rooms for filter
try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des salles.";
}

// Fetch all users for filter
try {
    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'user' ORDER BY username");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des utilisateurs.";
}

// Build query based on filters
$where_clause = "WHERE 1=1";
$params = [];

if ($room_filter > 0) {
    $where_clause .= " AND r.room_id = ?";
    $params[] = $room_filter;
}

if ($user_filter > 0) {
    $where_clause .= " AND r.user_id = ?";
    $params[] = $user_filter;
}

if (!empty($date_filter)) {
    $where_clause .= " AND r.reservation_date = ?";
    $params[] = $date_filter;
}

try {
    $stmt = $pdo->prepare("SELECT r.*, ro.name as room_name, u.username 
                          FROM reservations r 
                          JOIN rooms ro ON r.room_id = ro.id 
                          JOIN users u ON r.user_id = u.id 
                          {$where_clause}
                          ORDER BY r.reservation_date DESC, r.start_time DESC");
    $stmt->execute($params);
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des réservations.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - Admin</title>
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
                        <a class="nav-link active" href="reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
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
        <h2><i class="fas fa-calendar-check"></i> Gestion des réservations</h2>
        <p class="lead">Consultez et gérez toutes les réservations du système.</p>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $date_filter; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="room_id" class="form-label">Salle</label>
                        <select class="form-select" id="room_id" name="room_id">
                            <option value="0">Toutes les salles</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>" <?php echo ($room_filter == $room['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($room['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Utilisateur</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="0">Tous les utilisateurs</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($user_filter == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 me-2">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <a href="reservations.php" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Reservations Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> Liste des réservations</span>
                <span class="badge bg-primary"><?php echo count($reservations); ?> réservations</span>
            </div>
            <div class="card-body">
                <?php if (isset($reservations) && count($reservations) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Salle</th>
                                <th>Heures</th>
                                <th>Utilisateur</th>
                                <th>Objectif</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo formatDate($reservation['reservation_date']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['room_name']); ?></td>
                                <td><?php echo substr($reservation['start_time'], 0, 5); ?> - <?php echo substr($reservation['end_time'], 0, 5); ?></td>
                                <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                                <td>
                                    <a href="delete_reservation.php?id=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucune réservation trouvée pour les critères sélectionnés.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>