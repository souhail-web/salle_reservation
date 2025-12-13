<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
redirectIfNotLoggedIn();

$error = '';
$success = '';

// Fetch all rooms
try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des salles.";
}

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = intval($_POST['room_id']);
    $reservation_date = $_POST['reservation_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $purpose = trim($_POST['purpose']);
    
    // Validation
    if (empty($room_id) || empty($reservation_date) || empty($start_time) || empty($end_time)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (strtotime($reservation_date) < strtotime(date('Y-m-d'))) {
        $error = 'La date de réservation ne peut pas être dans le passé.';
    } elseif ($start_time >= $end_time) {
        $error = 'L\'heure de début doit être avant l\'heure de fin.';
    } else {
        try {
            // Check if the room is already booked for this time slot
            $stmt = $pdo->prepare("SELECT id FROM reservations 
                                  WHERE room_id = ? 
                                  AND reservation_date = ? 
                                  AND ((start_time < ? AND end_time > ?) 
                                  OR (start_time < ? AND end_time > ?)
                                  OR (start_time >= ? AND end_time <= ?))");
            $stmt->execute([$room_id, $reservation_date, $end_time, $start_time, $start_time, $end_time, $start_time, $end_time]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Cette salle est déjà réservée pour ce créneau horaire.';
            } else {
                // Create reservation
                $stmt = $pdo->prepare("INSERT INTO reservations (user_id, room_id, reservation_date, start_time, end_time, purpose) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $room_id, $reservation_date, $start_time, $end_time, $purpose]);
                $success = 'Réservation effectuée avec succès!';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la réservation.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver une Salle</title>
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
                        <a class="nav-link active" href="reserve.php"><i class="fas fa-calendar-plus"></i> Réserver une salle</a>
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
        <h2><i class="fas fa-calendar-plus"></i> Réserver une salle</h2>
        <p class="lead">Remplissez le formulaire ci-dessous pour réserver une salle.</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="room_id" class="form-label">Salle *</label>
                        <select class="form-select" id="room_id" name="room_id" required>
                            <option value="">Sélectionnez une salle</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>">
                                <?php echo htmlspecialchars($room['name']); ?> 
                                (<?php echo $room['capacity']; ?> personnes)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reservation_date" class="form-label">Date de réservation *</label>
                        <input type="date" class="form-control" id="reservation_date" name="reservation_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Heure de début *</label>
                                <select class="form-select" id="start_time" name="start_time" required>
                                    <option value="">Sélectionnez une heure</option>
                                    <?php foreach (getTimeOptions() as $time): ?>
                                    <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">Heure de fin *</label>
                                <select class="form-select" id="end_time" name="end_time" required>
                                    <option value="">Sélectionnez une heure</option>
                                    <?php foreach (getTimeOptions() as $time): ?>
                                    <option value="<?php echo $time; ?>"><?php echo $time; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Objectif de la réservation</label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="3" 
                                  placeholder="Réunion d'équipe, Présentation client, Formation, etc."></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check"></i> Réserver la salle
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('reservation_date').min = today;
        });
    </script>
</body>
</html>