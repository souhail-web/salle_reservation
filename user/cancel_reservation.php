<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
redirectIfNotLoggedIn();

// Get reservation ID
$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$reservation_id) {
    header('Location: dashboard.php');
    exit();
}

try {
    // Delete reservation if it belongs to the current user
    $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'Réservation annulée avec succès.';
    } else {
        $_SESSION['error'] = 'Impossible d\'annuler cette réservation.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur lors de l\'annulation de la réservation.';
}

header('Location: dashboard.php');
exit();
?>