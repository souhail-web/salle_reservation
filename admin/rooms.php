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

// Handle form submissions
$message = '';
$error = '';

// Add new room
if (isset($_POST['add_room'])) {
    $name = trim($_POST['name']);
    $capacity = intval($_POST['capacity']);
    $equipment = trim($_POST['equipment']);
    
    if (empty($name) || $capacity <= 0) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, equipment) VALUES (?, ?, ?)");
            $stmt->execute([$name, $capacity, $equipment]);
            $message = 'Salle ajoutée avec succès.';
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'ajout de la salle.';
        }
    }
}

// Update room
if (isset($_POST['update_room'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $capacity = intval($_POST['capacity']);
    $equipment = trim($_POST['equipment']);
    
    if (empty($name) || $capacity <= 0) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE rooms SET name = ?, capacity = ?, equipment = ? WHERE id = ?");
            $stmt->execute([$name, $capacity, $equipment, $id]);
            $message = 'Salle mise à jour avec succès.';
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour de la salle.';
        }
    }
}

// Delete room
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Salle supprimée avec succès.';
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression de la salle. Elle peut avoir des réservations associées.';
    }
}

// Fetch all rooms
try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
    $rooms = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Erreur lors du chargement des salles.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Salles - Admin</title>
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
                        <a class="nav-link active" href="rooms.php"><i class="fas fa-door-open"></i> Gestion des salles</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-door-open"></i> Gestion des salles</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="fas fa-plus"></i> Ajouter une salle
            </button>
        </div>
        
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
        
        <!-- Rooms Table -->
        <div class="card">
            <div class="card-body">
                <?php if (isset($rooms) && count($rooms) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Capacité</th>
                                <th>Équipements</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['name']); ?></td>
                                <td><?php echo $room['capacity']; ?> personnes</td>
                                <td><?php echo htmlspecialchars($room['equipment']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="editRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['name']); ?>', <?php echo $room['capacity']; ?>, '<?php echo htmlspecialchars($room['equipment']); ?>')">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <a href="?delete=<?php echo $room['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucune salle disponible. <a href="#" data-bs-toggle="modal" data-bs-target="#addRoomModal">Ajoutez votre première salle</a>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter une nouvelle salle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la salle *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacité (nombre de personnes) *</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="equipment" class="form-label">Équipements</label>
                            <textarea class="form-control" id="equipment" name="equipment" rows="3"></textarea>
                            <div class="form-text">Listez les équipements disponibles dans la salle (séparés par des virgules).</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="add_room" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier la salle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nom de la salle *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_capacity" class="form-label">Capacité (nombre de personnes) *</label>
                            <input type="number" class="form-control" id="edit_capacity" name="capacity" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_equipment" class="form-label">Équipements</label>
                            <textarea class="form-control" id="edit_equipment" name="equipment" rows="3"></textarea>
                            <div class="form-text">Listez les équipements disponibles dans la salle (séparés par des virgules).</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="update_room" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(id, name, capacity, equipment) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_capacity').value = capacity;
            document.getElementById('edit_equipment').value = equipment;
            var editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
            editModal.show();
        }
    </script>
</body>
</html>