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
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --accent: #8b5cf6;
            --text: #0f172a;
            --muted: #64748b;
            --card-radius: 18px;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(1200px 520px at 10% 10%, rgba(37, 99, 235, 0.18), transparent 60%),
                radial-gradient(980px 520px at 90% 25%, rgba(139, 92, 246, 0.18), transparent 55%),
                linear-gradient(180deg, #f8fafc 0%, #eef2ff 45%, #f8fafc 100%);
            color: var(--text);
        }

        .dashboard-shell {
            position: relative;
            overflow: hidden;
        }

        .navbar-glass {
            background: rgba(255, 255, 255, 0.78) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(2, 6, 23, 0.08);
        }

        .navbar-glass .navbar-brand {
            font-weight: 800;
            letter-spacing: 0.2px;
            color: var(--text) !important;
        }

        .nav-pill {
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            color: #0f172a !important;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .nav-pill:hover {
            background: rgba(37, 99, 235, 0.08);
            transform: translateY(-1px);
        }

        .nav-pill.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(139, 92, 246, 0.12));
            font-weight: 700;
        }

        .hero {
            border-radius: var(--card-radius);
            background:
                radial-gradient(900px 260px at 15% 20%, rgba(37, 99, 235, 0.18), transparent 60%),
                radial-gradient(800px 260px at 80% 10%, rgba(139, 92, 246, 0.18), transparent 55%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.9));
            border: 1px solid rgba(2, 6, 23, 0.06);
            box-shadow: 0 20px 55px rgba(2, 6, 23, 0.10);
            overflow: hidden;
        }

        .hero .hero-title {
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(2, 6, 23, 0.06);
            color: #0f172a;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .e-card {
            border: 1px solid rgba(2, 6, 23, 0.08);
            border-radius: var(--card-radius);
            box-shadow: 0 16px 40px rgba(2, 6, 23, 0.08);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.92);
        }

        .e-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(2, 6, 23, 0.06);
            padding: 1rem 1.25rem;
        }

        .e-card .card-body {
            padding: 1.25rem;
        }

        .btn-gradient {
            border: none;
            color: #fff !important;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.25);
            border-radius: 12px;
        }

        .btn-gradient:hover {
            filter: brightness(1.03);
            transform: translateY(-1px);
        }

        .btn-soft {
            background: rgba(15, 23, 42, 0.05);
            border: 1px solid rgba(2, 6, 23, 0.08);
            color: #0f172a;
            border-radius: 12px;
        }

        .btn-outline-danger {
            border-radius: 12px;
        }

        .btn-outline-danger:hover {
            transform: translateY(-1px);
        }

        .btn:focus-visible,
        .nav-pill:focus-visible,
        .btn-close:focus-visible,
        .navbar-toggler:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.35);
            outline-offset: 2px;
            box-shadow: none;
        }

        .alert {
            border-radius: 14px;
            border: 1px solid rgba(2, 6, 23, 0.06);
            box-shadow: 0 10px 25px rgba(2, 6, 23, 0.06);
        }

        .alert-success {
            border-left: 4px solid #22c55e;
        }

        .alert-danger {
            border-left: 4px solid #ef4444;
        }

        .table-modern {
            margin: 0;
        }

        .table-modern thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            border-bottom: 1px solid rgba(2, 6, 23, 0.08) !important;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            background: rgba(15, 23, 42, 0.02);
        }

        .table-modern tbody td {
            vertical-align: middle;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }

        .table-modern tbody tr {
            transition: background 0.15s ease;
        }

        .table-modern tbody tr:hover {
            background: rgba(37, 99, 235, 0.04);
        }

        .cell-clip {
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .room-item {
            border: 1px solid rgba(2, 6, 23, 0.06);
            border-radius: 14px;
            padding: 0.9rem 1rem;
            background: rgba(255, 255, 255, 0.9);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .room-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 35px rgba(2, 6, 23, 0.10);
        }

        .muted {
            color: var(--muted);
        }
    </style>
</head>
<body class="dashboard-shell">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-glass sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-calendar-check me-2" style="color: var(--primary);"></i>
                Réservation de Salles
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-pill active" href="dashboard.php"><i class="fas fa-home me-2"></i>Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-pill" href="reserve.php"><i class="fas fa-calendar-plus me-2"></i>Réserver</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-pill" href="schedule.php"><i class="fas fa-calendar-alt me-2"></i>Planning</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="me-3 align-self-center muted">
                        Bonjour, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </span>
                    <a href="../logout.php" class="btn btn-soft btn-sm"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4 py-lg-5">
        <div class="hero p-4 p-lg-5 mb-4 mb-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="hero-badge mb-3">
                        <i class="fas fa-sparkles" style="color: var(--primary);"></i>
                        Tableau de bord
                    </div>
                    <h1 class="hero-title h2 h1-lg mb-2">
                        Bienvenue, <span style="color: var(--primary);"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </h1>
                    <p class="mb-0 muted">
                        Gérez vos réservations, planifiez vos prochaines réunions et gardez une vue claire sur votre agenda.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                        <a href="reserve.php" class="btn btn-gradient">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Nouvelle réservation
                        </a>
                        <a href="schedule.php" class="btn btn-soft">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Voir planning
                        </a>
                    </div>
                </div>
            </div>
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
        
        <div class="row g-4">
            <div class="col-12">
                <div class="card e-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="hero-badge">
                                <i class="fas fa-history" style="color: var(--primary);"></i>
                                Vos réservations
                            </span>
                        </div>
                        <a href="reserve.php" class="btn btn-sm btn-gradient">
                            <i class="fas fa-plus me-2"></i>
                            Ajouter
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($reservations) && count($reservations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-modern table-hover align-middle">
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
                                        <td>
                                            <div class="fw-bold">
                                                <?php echo htmlspecialchars($reservation['room_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="muted">
                                                <?php echo formatDate($reservation['reservation_date']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border" style="border-color: rgba(2, 6, 23, 0.10) !important;">
                                                <i class="far fa-clock me-1" style="color: var(--primary);"></i>
                                                <?php echo substr($reservation['start_time'], 0, 5); ?> - <?php echo substr($reservation['end_time'], 0, 5); ?>
                                            </span>
                                        </td>
                                        <td class="cell-clip" title="<?php echo htmlspecialchars($reservation['purpose']); ?>">
                                            <?php echo htmlspecialchars($reservation['purpose']); ?>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-soft">
                                                    <i class="fas fa-edit me-1" style="color: #f59e0b;"></i>
                                                    Modifier
                                                </a>
                                                <a href="cancel_reservation.php?id=<?php echo $reservation['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                    <i class="fas fa-times me-1"></i>
                                                    Annuler
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="p-4 rounded-3" style="background: rgba(15, 23, 42, 0.03); border: 1px solid rgba(2, 6, 23, 0.06);">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(37, 99, 235, 0.12);">
                                        <i class="fas fa-info" style="color: var(--primary);"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold mb-1">Aucune réservation pour le moment</div>
                                    <div class="muted mb-3">Créez votre première réservation en quelques clics.</div>
                                    <a href="reserve.php" class="btn btn-gradient btn-sm">
                                        <i class="fas fa-calendar-plus me-2"></i>
                                        Réserver une salle
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>