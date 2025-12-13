<?php
$pageTitle = 'Détails de la salle';
require_once 'config/db.php';
require_once 'includes/header.php';

$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$autoscroll_reserve = isset($_GET['reserve']) && (string)$_GET['reserve'] === '1';

$room = null;
$error = '';

if ($room_id <= 0) {
    $error = 'Salle introuvable.';
} else {
    try {
        $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->execute([$room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$room) {
            $error = 'Salle introuvable.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement de la salle.';
    }
}

$fallback_images = [
    'assets/images/a.jfif',
    'assets/images/bjfif.jfif',
    'assets/images/c.jfif',
    'assets/images/d.jfif',
    'assets/images/e.jfif',
    'assets/images/fjfif.jfif',
];

if ($room) {
    $capacity = (int)($room['capacity'] ?? 0);
    $equipment = (string)($room['equipment'] ?? '');

    $features = array_values(array_filter(array_map('trim', explode(',', $equipment))));
    if (count($features) === 0) {
        $features = ['Wifi', 'Climatisation'];
    }

    if ($capacity >= 80) {
        $description = 'Grand espace adapté aux conférences et présentations, avec des équipements pour vos événements.';
    } elseif ($capacity >= 25) {
        $description = 'Salle spacieuse idéale pour des réunions d\'équipe, formations et ateliers collaboratifs.';
    } else {
        $description = 'Salle confortable pour vos réunions et rendez-vous professionnels.';
    }

    $price = round(49.99 + max(0, $capacity) * 5, 2);
    $image = $fallback_images[(int)($room_id % max(1, count($fallback_images)))];

    $room['features'] = $features;
    $room['description'] = $description;
    $room['price'] = $price;
    $room['image'] = $image;
}

$reserveHref = ($room && isset($_SESSION['user_id']))
    ? 'user/reserve.php?room_id=' . (int)$room_id
    : ($room ? 'login.php?redirect=' . urlencode('user/reserve.php?room_id=' . (int)$room_id) : 'login.php');
?>

<style>
    .room-hero {
        background:
            radial-gradient(1200px 520px at 10% 10%, rgba(37, 99, 235, 0.18), transparent 60%),
            radial-gradient(980px 520px at 90% 25%, rgba(139, 92, 246, 0.18), transparent 55%),
            linear-gradient(180deg, #f8fafc 0%, #eef2ff 45%, #f8fafc 100%);
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(2, 6, 23, 0.06);
        box-shadow: 0 18px 60px rgba(2, 6, 23, 0.10);
    }
</style>

<section class="room-hero py-10 md:py-14">
    <div class="container mx-auto px-4 max-w-6xl">
        <?php if ($error): ?>
            <div class="glass-panel rounded-2xl p-6 md:p-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Salle</h1>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($error); ?></p>
                <a href="home.php#salles" class="inline-flex items-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                    Retour aux salles
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <div class="glass-panel rounded-2xl overflow-hidden">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-72 md:h-96 object-cover">
                        <?php if (isset($room['price'])): ?>
                            <div class="absolute top-4 right-4">
                                <span class="bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold px-4 py-2 rounded-full shadow-lg">
                                    <?php echo number_format((float)$room['price'], 2, ',', ' '); ?> €
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-sm font-semibold border border-blue-100">
                            Détails
                        </span>
                        <span class="text-sm text-gray-500">Salle #<?php echo (int)$room['id']; ?></span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-3">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </h1>

                    <p class="text-gray-600 text-lg leading-relaxed mb-6">
                        <?php echo htmlspecialchars($room['description']); ?>
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4">
                            <div class="text-sm text-gray-500 mb-1">Capacité</div>
                            <div class="text-xl font-bold text-gray-900"><?php echo (int)$room['capacity']; ?> personnes</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4">
                            <div class="text-sm text-gray-500 mb-1">Équipements</div>
                            <div class="text-gray-900 font-semibold"><?php echo htmlspecialchars(implode(', ', $room['features'])); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($room['features'])): ?>
                        <div class="mb-7">
                            <div class="text-sm font-semibold text-gray-700 mb-3">Points forts</div>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($room['features'] as $feature): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-900/5 text-gray-800 text-sm border border-gray-200">
                                        <?php echo htmlspecialchars($feature); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="reserve-section" class="flex flex-col sm:flex-row gap-3">
                        <a href="<?php echo htmlspecialchars($reserveHref); ?>" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold shadow-lg hover:brightness-105 transition">
                            Réserver cette salle
                        </a>
                        <a href="home.php#salles" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white/70 text-gray-900 font-semibold border border-gray-200 hover:bg-white transition">
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($autoscroll_reserve && !$error): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var target = document.getElementById('reserve-section');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
