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

// Get reservation ID
$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$reservation_id) {
    header('Location: reservations.php');
    exit();
}

try {
    // Delete reservation
    $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->execute([$reservation_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'Réservation supprimée avec succès.';
    } else {
        $_SESSION['error'] = 'Impossible de supprimer cette réservation.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur lors de la suppression de la réservation.';
}

header('Location: reservations.php');
exit();
?>