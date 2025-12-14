<?php
session_start();
$pageTitle = 'Tableau de bord';
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

require_once '../includes/header.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p class="text-gray-600">Gérez vos réservations et planifiez vos prochaines réunions.</p>
    </div>

    <!-- Messages d'alerte -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?php echo $message; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?php echo $error; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="reserve.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Nouvelle réservation</h3>
                    <p class="mt-1 text-sm text-gray-500">Réserver une salle de réunion</p>
                </div>
            </div>
        </a>

        <a href="schedule.php" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Voir le planning</h3>
                    <p class="mt-1 text-sm text-gray-500">Consulter les disponibilités</p>
                </div>
            </div>
        </a>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Prochaine réunion</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php 
                        $next_meeting = $reservations[0] ?? null;
                        if ($next_meeting) {
                            $date = new DateTime($next_meeting['reservation_date']);
                            echo 'Le ' . $date->format('d/m/Y') . ' à ' . substr($next_meeting['start_time'], 0, 5);
                        } else {
                            echo 'Aucune réunion à venir';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-amber-100 p-3 rounded-lg mr-4">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Total des réservations</h3>
                    <p class="mt-1 text-sm text-gray-500"><?php echo count($reservations); ?> réservation(s)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des réservations -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="h-5 w-5 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Mes réservations
            </h2>
        </div>
        
        <?php if (isset($reservations) && count($reservations) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Salle
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Heure
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Objectif
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reservations as $reservation): 
                            $reservation_date = new DateTime($reservation['reservation_date']);
                            $start_time = new DateTime($reservation['start_time']);
                            $end_time = new DateTime($reservation['end_time']);
                            $interval = $start_time->diff($end_time);
                            $now = new DateTime();
                            $is_past = ($reservation_date < $now->setTime(0, 0, 0));
                            $is_today = ($reservation_date->format('Y-m-d') === $now->format('Y-m-d'));
                            $status_class = $is_past ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800';
                            $status_text = $is_past ? 'Terminée' : ($is_today ? 'Aujourd\'hui' : 'À venir');
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reservation['room_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $reservation['purpose']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $reservation_date->format('d/m/Y'); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo ucfirst(strftime('%A', $reservation_date->getTimestamp())); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $start_time->format('H:i'); ?> - <?php echo $end_time->format('H:i'); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php 
                                        $hours = floor($interval->h + ($interval->i / 60));
                                        $minutes = $interval->i % 60;
                                        echo $hours . 'h' . ($minutes > 0 ? $minutes : '');
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($reservation['purpose']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (!$is_past): ?>
                                        <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Modifier</a>
                                        <a href="cancel_reservation.php?id=<?php echo $reservation['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">Annuler</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white px-4 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune réservation</h3>
                <p class="mt-1 text-sm text-gray-500">Vous n'avez encore effectué aucune réservation.</p>
                <div class="mt-6">
                    <a href="reserve.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Nouvelle réservation
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

<script>
// Confirmation de suppression
function confirmDelete() {
    return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');
}
</script>