<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$response = ['success' => false];

// Images par défaut
$fallback_images = [
    'assets/images/a.jfif',
    'assets/images/bjfif.jfif',
    'assets/images/c.jfif',
    'assets/images/d.jfif',
    'assets/images/e.jfif',
    'assets/images/fjfif.jfif',
];

if (isset($_GET['room_id']) && is_numeric($_GET['room_id'])) {
    $room_id = (int)$_GET['room_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($room) {
            // Utiliser l'image de la salle si elle existe, sinon utiliser une image par défaut
            $image = !empty($room['image']) ? $room['image'] : $fallback_images[$room_id % count($fallback_images)];
            
            $response = [
                'success' => true,
                'room' => [
                    'id' => $room['id'],
                    'name' => $room['name'],
                    'description' => $room['description'] ?? '',
                    'capacity' => $room['capacity'] ?? 0,
                    'image' => $image,
                    'features' => $room['features'] ?? ''
                ]
            ];
        }
    } catch (PDOException $e) {
        $response['error'] = 'Erreur de base de données';
    }
}

echo json_encode($response);
?>
