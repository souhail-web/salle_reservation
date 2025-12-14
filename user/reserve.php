<?php
session_start();
$pageTitle = 'Réserver une salle';

require_once '../config/db.php';
require_once '../includes/functions.php';

redirectIfNotLoggedIn();

$error = '';
$success = '';

$preselected_room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$preselected_room_name = isset($_GET['room']) ? trim((string)$_GET['room']) : '';

// Images par défaut
$fallback_images = [
    'assets/images/a.jfif',
    'assets/images/bjfif.jfif',
    'assets/images/c.jfif',
    'assets/images/d.jfif',
    'assets/images/e.jfif',
    'assets/images/fjfif.jfif',
];

// Fetch all rooms
try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
    $db_rooms = $stmt->fetchAll();
    $rooms = [];
    
    // Préparer les données des salles avec les images par défaut
    foreach ($db_rooms as $r) {
        $id = (int)($r['id'] ?? 0);
        $r['image'] = $fallback_images[$id % count($fallback_images)];
        $rooms[] = $r;
        
        // Si c'est la salle présélectionnée, on la met à jour
        if ($preselected_room_id > 0 && $id === $preselected_room_id) {
            $room = $r;
        } elseif ($preselected_room_name !== '' && isset($r['name']) && 
                 strcasecmp((string)$r['name'], $preselected_room_name) === 0) {
            $preselected_room_id = $id;
            $room = $r;
        }
    }
    
    // Si on a un ID mais qu'on n'a pas encore trouvé la salle, on fait une requête directe
    if ($preselected_room_id > 0 && !isset($room)) {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$preselected_room_id]);
        $room = $stmt->fetch();
        if ($room) {
            $room['image'] = $fallback_images[$preselected_room_id % count($fallback_images)];
        }
    }
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
                $_SESSION['message'] = 'Réservation effectuée avec succès!';
                header('Location: dashboard.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la réservation.';
        }
    }
}

require_once '../includes/header.php';
?>

<section class="bg-gradient-to-b from-blue-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">Réserver une salle</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                <?php if (isset($room['name'])): ?>
                    Remplissez le formulaire ci-dessous pour réserver la salle <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($room['name']); ?></span>
                <?php else: ?>
                    Remplissez le formulaire ci-dessous pour réserver une salle
                <?php endif; ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mb-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo $error; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg mb-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulaire de réservation -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900">Détails de la réservation</h2>
                        <p class="text-sm text-gray-500 mt-1">Remplissez les informations requises pour effectuer une réservation</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" class="space-y-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-gray-700" for="room_id">Salle à réserver <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-3">Sélectionnez la salle que vous souhaitez réserver</p>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <select class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                            id="room_id" 
                                            name="room_id" 
                                            required>
                                        <option value="">Sélectionnez une salle</option>
                                        <?php foreach ($rooms as $r): ?>
                                        <option value="<?php echo $r['id']; ?>" 
                                                data-image="<?php echo !empty($r['image']) ? htmlspecialchars($r['image']) : ''; ?>"
                                                data-capacity="<?php echo (int)$r['capacity']; ?>"
                                                data-description="<?php echo isset($r['description']) ? htmlspecialchars($r['description']) : ''; ?>"
                                                <?php echo (isset($room['id']) && $room['id'] == $r['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($r['name']); ?> 
                                            (<?php echo (int)$r['capacity']; ?> personnes)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-gray-700" for="reservation_date">Date de réservation <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-3">Sélectionnez la date de votre réservation</p>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input type="date" 
                                           class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           id="reservation_date" 
                                           name="reservation_date" 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           value="<?php echo isset($_POST['reservation_date']) ? htmlspecialchars($_POST['reservation_date']) : ''; ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <label class="block text-sm font-semibold text-gray-700" for="start_time">Heure de début <span class="text-red-500">*</span></label>
                                    <p class="text-xs text-gray-500 mb-3">Sélectionnez l'heure de début</p>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <select class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                id="start_time" 
                                                name="start_time" 
                                                required>
                                            <option value="">Sélectionnez une heure</option>
                                            <?php 
                                            $selected_start = $_POST['start_time'] ?? '';
                                            foreach (getTimeOptions() as $time): 
                                            ?>
                                            <option value="<?php echo $time; ?>" <?php echo ($selected_start === $time) ? 'selected' : ''; ?>>
                                                <?php echo $time; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="space-y-1">
                                    <label class="block text-sm font-semibold text-gray-700" for="end_time">Heure de fin <span class="text-red-500">*</span></label>
                                    <p class="text-xs text-gray-500 mb-3">Sélectionnez l'heure de fin</p>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <select class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                id="end_time" 
                                                name="end_time" 
                                                required>
                                            <option value="">Sélectionnez une heure</option>
                                            <?php 
                                            $selected_end = $_POST['end_time'] ?? '';
                                            foreach (getTimeOptions() as $time): 
                                            ?>
                                            <option value="<?php echo $time; ?>" <?php echo ($selected_end === $time) ? 'selected' : ''; ?>>
                                                <?php echo $time; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <label class="block text-sm font-semibold text-gray-700" for="purpose">Objet de la réservation</label>
                                <p class="text-xs text-gray-500 mb-3">Décrivez brièvement l'objectif de votre réservation</p>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 text-gray-400">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                    </div>
                                    <textarea class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                              id="purpose" 
                                              name="purpose" 
                                              rows="4" 
                                              placeholder="Ex: Réunion d'équipe, Présentation client, Formation, etc."><?php echo isset($_POST['purpose']) ? htmlspecialchars($_POST['purpose']) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-4 pt-2">
                                <button type="submit" class="flex-1 inline-flex items-center justify-center px-6 py-4 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                    Réserver maintenant
                                </button>
                                <a href="dashboard.php" class="inline-flex items-center justify-center px-6 py-4 rounded-xl bg-white text-gray-900 font-semibold border border-gray-200 hover:bg-gray-50 transition">
                                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Retour
                                </a>
                            </div>
                            
                            <p class="text-xs text-gray-500 text-center mt-4">
                                En effectuant cette réservation, vous acceptez nos conditions d'utilisation.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Détails de la salle -->
            <div id="room-details" class="lg:sticky lg:top-6 h-fit">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="h-48 bg-gray-100 overflow-hidden">
                        <img id="room-image" 
                             src="<?php echo !empty($room['image']) ? '../' . htmlspecialchars($room['image']) : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGNsYXNzPSJoLTE2IHctMTYgdGV4dC1ncmF5LTQwMCIgZmlsbD0ibm9uZSIgdmlld0JveD0iMCAwIDI0IDI0IiBzdHJva2U9ImN1cnJlbnRDb2xvciI+PHBhdGggc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2Utd2lkdGg9IjEuNSIgZD0iTTQgNWEyIDIgMCAwMTItMmgxNGEyIDIgMCAwMTIgMnYyYTIgMiAwIDAxLTIgMkg2YTIgMiAwIDAxLTItMlY1TTQgMTNhMiAyIDAgMDEyLTJoMm0wMGgxMGEyIDIgMCAwMTIgMnYyYTIgMiAwIDAxLTIgMkg2YTIgMiAwIDAxLTItMnYtMk02IDE5YTIgMiAwIDEwMCA0aDEyYTIgMiAwIDAwMC00SDZ6Ii8+PC9zdmc+'; ?>" 
                             alt="<?php echo isset($room['name']) ? htmlspecialchars($room['name']) : 'Salle de réunion'; ?>" 
                             class="w-full h-48 md:h-64 object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                    <div class="p-6">
                        <div id="room-info">
                            <?php if (isset($room['name'])): ?>
                            <div class="flex justify-between items-start mb-4">
                                <h3 id="room-name" class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($room['name']); ?></h3>
                                <?php if (isset($room['capacity'])): ?>
                                <span id="room-capacity" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo (int)$room['capacity']; ?> places
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($room['description']) && !empty($room['description'])): ?>
                            <p id="room-description" class="text-gray-600 mb-5">
                                <?php echo htmlspecialchars($room['description']); ?>
                            </p>
                            <?php endif; ?>
                        
                        <div class="space-y-4 pt-4 border-t border-gray-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-6 w-6 text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-700">Besoin d'aide ?</p>
                                    <p class="text-sm text-gray-500 mt-1">Contactez-nous au <a href="tel:+33123456789" class="text-blue-600 hover:text-blue-800">+212 660-227614</a></p>
                                    <p class="text-sm text-gray-500">ou par email à <a href="mailto:contact@votresite.com" class="text-blue-600 hover:text-blue-800">contact@votresite.com</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 bg-blue-50 border border-blue-100 rounded-2xl p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Information importante</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Les réservations sont soumises à validation. Vous recevrez une confirmation par email une fois votre demande traitée.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('reservation_date').min = today;

        // Mise à jour dynamique des détails de la salle
        const roomSelect = document.getElementById('room_id');
        if (roomSelect) {
            // Fonction pour mettre à jour les détails de la salle
            function updateRoomDetails(roomId) {
                if (!roomId) return;

                // Récupérer les détails de la salle via AJAX
                fetch(`../api/get_room_details.php?room_id=${roomId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const room = data.room;
                            // Mettre à jour l'image
                            const roomImage = document.getElementById('room-image');
                            if (roomImage) {
                                // Vérifier si le chemin de l'image est déjà complet ou non
                                const imagePath = room.image.startsWith('assets/') ? `../${room.image}` : 
                                               (room.image ? `../assets/images/${room.image}` : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGNsYXNzPSJoLTE2IHctMTYgdGV4dC1ncmF5LTQwMCIgZmlsbD0ibm9uZSIgdmlld0JveD0iMCAwIDI0IDI0IiBzdHJva2U9ImN1cnJlbnRDb2xvciI+PHBhdGggc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2Utd2lkdGg9IjEuNSIgZD0iTTQgNWEyIDIgMCAwMTItMmgxNGEyIDIgMCAwMTIgMnYyYTIgMiAwIDAxLTIgMkg2YTIgMiAwIDAxLTItMlY1TTQgMTNhMiAyIDAgMDEyLTJoMm0wMGgxMGEyIDIgMCAwMTIgMnYyYTIgMiAwIDAxLTIgMkg2YTIgMiAwIDAxLTItMnYtMk02IDE5YTIgMiAwIDEwMCA0aDEyYTIgMiAwIDAwMC00SDZ6Ii8+PC9zdmc+');
                                
                                roomImage.src = imagePath;
                                roomImage.alt = room.name || 'Salle de réunion';
                                
                                // Forcer le rechargement de l'image en cas de problème de cache
                                roomImage.onerror = function() {
                                    this.onerror = null;
                                    this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGNsYXNzPSJoLTE2IHctMTYgdGV4dC1ncmF5LTQwMCIgZmlsbD0ibm9uZSIgdmlld0JveD0iMCAwIDI0IDI0IiBzdHJva2U9ImN1cnJlbnRDb2xvciI+PHBhdGggc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBzdHJva2Utd2lkdGg9IjEuNSIgZD0iTTQgNWEyIDIgMCAwMTItMmgxNGEyIDIgMCAwMTIgMnYyYTIgMiAwIDAxLTIgMkg2YTIgMiAwIDAxLTItMlY1TTQgMTNhMiAyIDAgMDEyLTJoMm0wMGgxMGEyIDIgMCAwMTIgMnYyYTIgMiAwIDAxLTIgMkg2YTIgMiAwIDAxLTItMnYtMk02IDE5YTIgMiAwIDEwMCA0aDEyYTIgMiAwIDAwMC00SDZ6Ii8+PC9zdmc+';
                                };
                            }

                            // Mettre à jour le nom et la capacité
                            const roomName = document.getElementById('room-name');
                            const roomCapacity = document.getElementById('room-capacity');
                            const roomDescription = document.getElementById('room-description');

                            if (roomName) roomName.textContent = room.name || 'Salle de réunion';
                            if (roomCapacity) {
                                roomCapacity.textContent = `${room.capacity || 0} places`;
                                roomCapacity.style.display = room.capacity ? 'inline-flex' : 'none';
                            }
                            if (roomDescription) {
                                roomDescription.textContent = room.description || '';
                                roomDescription.style.display = room.description ? 'block' : 'none';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des détails de la salle:', error);
                    });
            }

            // Écouter les changements de sélection
            roomSelect.addEventListener('change', function() {
                updateRoomDetails(this.value);
            });

            // Mettre à jour les détails au chargement si une salle est déjà sélectionnée
            if (roomSelect.value) {
                updateRoomDetails(roomSelect.value);
            }
        }
    });
</script>