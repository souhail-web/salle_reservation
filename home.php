<?php
$pageTitle = 'Accueil';
require_once 'config/db.php';
require_once 'includes/header.php';

$rooms = [];
$rooms_error = '';

$fallback_images = [
    'assets/images/a.jfif',
    'assets/images/bjfif.jfif',
    'assets/images/c.jfif',
    'assets/images/d.jfif',
    'assets/images/e.jfif',
    'assets/images/fjfif.jfif',
];

try {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name");
    $db_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($db_rooms as $r) {
        $id = (int)($r['id'] ?? 0);
        $name = (string)($r['name'] ?? '');
        $capacity = (int)($r['capacity'] ?? 0);
        $equipment = (string)($r['equipment'] ?? '');

        $features = array_values(array_filter(array_map('trim', explode(',', $equipment))));
        if (count($features) === 0) {
            $features = ['Wifi', 'Climatisation'];
        }

        // Limiter la capacité à 40 personnes maximum
        $display_capacity = min($capacity, 40);
        
        if ($display_capacity >= 30) {
            $description = 'Grand espace adapté aux conférences et présentations, avec des équipements pour vos événements (jusqu\'à 40 personnes).';
        } elseif ($display_capacity >= 15) {
            $description = 'Salle spacieuse idéale pour des réunions d\'équipe, formations et ateliers collaboratifs (jusqu\'à 30 personnes).';
        } else {
            $description = 'Salle confortable pour vos réunions et rendez-vous professionnels (jusqu\'à 15 personnes).';
        }

        $price = round(49.99 + max(0, $capacity) * 5, 2);
        $image = $fallback_images[(int)($id % max(1, count($fallback_images)))];

        $rooms[] = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'capacity' => min($capacity, 40), // Limiter à 40 personnes maximum
            'image' => $image,
            'features' => $features,
        ];
    }
} catch (PDOException $e) {
    $rooms_error = 'Erreur lors du chargement des salles.';
}
?>

<style>
/* Animations personnalisées */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Styles globaux améliorés */
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary: #f8fafc;
    --accent: #f59e0b;
    --text: #1e293b;
    --text-light: #64748b;
}

* {
    scroll-behavior: smooth;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    overflow-x: hidden;
}

/* Classes d'animations */
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse 2s ease-in-out infinite;
}

.animate-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    background-size: 1000px 100%;
    animation: shimmer 2s infinite;
}

.gradient-bg {
    background: linear-gradient(-45deg, #3b82f6, #8b5cf6, #10b981, #f59e0b);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
}

/* Effets de survol améliorés */
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* Card styles améliorés */
.room-card {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    background: white;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.room-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}

.room-card:hover::before {
    transform: scaleX(1);
}

.room-card .image-container {
    position: relative;
    height: 100%;
    width: 100%;
    flex-shrink: 0; /* Empêche la réduction de la hauteur */
}

.room-card img {
    transition: opacity 0.5s ease, transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
}

.room-card img.loaded {
    opacity: 1;
}

.image-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, #f0f0f0 0%, #e0e0e0 50%, #f0f0f0 100%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite linear;
    border-radius: 0.75rem 0.75rem 0 0;
}

.room-card:hover img {
    transform: scale(1.1);
}

/* Badge styles */
.feature-badge {
    display: inline-block;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.feature-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Button styles améliorés */
.btn-primary {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    padding: 14px 32px;
    border-radius: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-primary::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.7s;
}

.btn-primary:hover::after {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
}

/* Section styles */
.section-title {
    position: relative;
    display: inline-block;
    margin-bottom: 2rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    border-radius: 2px;
}

#salles {
    position: relative;
    background:
        radial-gradient(900px 420px at 10% 10%, rgba(37, 99, 235, 0.16), transparent 60%),
        radial-gradient(780px 380px at 90% 20%, rgba(139, 92, 246, 0.16), transparent 58%),
        linear-gradient(180deg, rgba(241, 245, 249, 0.95) 0%, rgba(226, 232, 240, 0.55) 100%);
}

#salles::before {
    content: '';
    position: absolute;
    width: 520px;
    height: 520px;
    left: -220px;
    top: 40px;
    background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.35), rgba(59, 130, 246, 0) 70%);
    filter: blur(18px);
    pointer-events: none;
    z-index: 0;
}

#salles::after {
    content: '';
    position: absolute;
    width: 560px;
    height: 560px;
    right: -260px;
    bottom: 10px;
    background: radial-gradient(circle at 70% 70%, rgba(168, 85, 247, 0.28), rgba(168, 85, 247, 0) 70%);
    filter: blur(20px);
    pointer-events: none;
    z-index: 0;
}

#salles > .container {
    position: relative;
    z-index: 1;
}

/* Testimonial styles */
.testimonial-card {
    position: relative;
    padding: 2rem;
    border-radius: 16px;
    background: white;
    transition: all 0.3s ease;
}

.testimonial-card::before {
    content: '"';
    position: absolute;
    top: -20px;
    left: 20px;
    font-size: 80px;
    color: var(--primary);
    opacity: 0.1;
    font-family: Georgia, serif;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

/* Hero section améliorée */
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(30, 41, 59, 0.7) 100%);
}

.hero-content {
    position: relative;
    z-index: 10;
}

/* Process steps améliorés */
.process-steps {
    position: relative;
}

@media (min-width: 768px) {
    .process-steps::before {
        content: '';
        position: absolute;
        top: 30px;
        left: calc(100% / 6);
        right: calc(100% / 6);
        height: 2px;
        background: linear-gradient(90deg, rgba(37, 99, 235, 0.22), rgba(245, 158, 11, 0.18));
        border-radius: 999px;
    }
}

.process-step {
    position: relative;
    z-index: 1;
    padding-top: 0.25rem;
}

.process-step .step-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
    margin: 0 auto 1.5rem;
    position: relative;
    transition: all 0.3s ease;
    border: 4px solid rgba(255, 255, 255, 0.95);
    box-shadow: 0 10px 25px rgba(2, 6, 23, 0.12);
}

.process-step:hover .step-number {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
}

/* Loading animation pour les images */
.image-loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

/* Responsive design */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .section-title::after {
        width: 40px;
    }
}
</style>

<!-- Bannière principale avec effets visuels -->
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden">
    <!-- Arrière-plan avec overlay gradient -->
    <div class="absolute inset-0 z-0">
        <div class="hero-overlay"></div>
        <img src="assets/images/a.jfif" 
             alt="Salle de conférence élégante" 
             class="w-full h-full object-cover opacity-40 transform scale-110"
             loading="eager">
        <!-- Particules décoratives -->
        <div class="absolute inset-0">
            <div class="absolute top-1/4 left-1/4 w-4 h-4 bg-blue-400 rounded-full animate-pulse-slow"></div>
            <div class="absolute top-1/3 right-1/4 w-6 h-6 bg-purple-400 rounded-full animate-float"></div>
            <div class="absolute bottom-1/4 left-1/3 w-3 h-3 bg-cyan-400 rounded-full animate-pulse-slow" style="animation-delay: 0.5s"></div>
        </div>
    </div>
    
    <!-- Contenu principal -->
    <div class="container mx-auto px-4 relative z-10 hero-content">
        <div class="max-w-4xl mx-auto text-center animate-fade-in-up">
            <div class="inline-flex items-center mb-6 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full border border-white/20">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-2"></span>
                <span class="text-sm text-white/90 font-medium">+500 entreprises nous font confiance</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight text-white">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-300 to-cyan-200">L'excellence</span>
                <br>
                <span class="text-white">pour vos événements d'entreprise</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-blue-100 mb-10 max-w-3xl mx-auto leading-relaxed">
                Des espaces premium équipés des dernières technologies pour des réunions, formations et séminaires inoubliables
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4 animate-fade-in-up" style="animation-delay: 0.3s">
                <a href="#salles" 
                   class="btn-primary text-white font-semibold text-lg inline-flex items-center justify-center group">
                    <span>Explorer nos espaces</span>
                    <svg class="w-5 h-5 ml-3 transform group-hover:translate-x-2 transition-transform" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" 
                   class="bg-transparent border-2 border-white/30 text-white hover:bg-white/10 font-semibold py-4 px-8 rounded-lg text-lg transition-all duration-300 backdrop-blur-sm hover:border-white/50">
                    Créer un compte gratuit
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Statistiques -->
            <div class="mt-16 grid grid-cols-3 gap-8 max-w-2xl mx-auto">
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">50+</div>
                    <div class="text-sm text-blue-200">Espaces disponibles</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">98%</div>
                    <div class="text-sm text-blue-200">Satisfaction client</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white mb-2">24/7</div>
                    <div class="text-sm text-blue-200">Support disponible</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <a href="#how-it-works" class="text-white/60 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </a>
    </div>
</section>

<!-- Processus -->
<section id="how-it-works" class="py-20 md:py-24 bg-gradient-to-b from-white to-gray-50/50">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-12 md:mb-20">
            <span class="inline-block text-blue-600 text-sm font-semibold tracking-widest uppercase mb-4 animate-fade-in-up">SIMPLICITÉ & EFFICACITÉ</span>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-5 animate-fade-in-up" style="animation-delay: 0.1s">
                Comment ça <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">fonctionne</span>
            </h2>
            <div class="section-title"></div>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed animate-fade-in-up" style="animation-delay: 0.2s">
                Un processus de réservation pensé pour vous faire gagner du temps, en seulement 3 étapes simples
            </p>
        </div>
        
        <div class="process-steps grid md:grid-cols-3 gap-8 md:gap-10">
            <!-- Étape 1 -->
            <div class="process-step text-center group">
                <div class="step-number group-hover:shadow-xl transition-all duration-500">
                    1
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative p-7 rounded-2xl bg-white/90 backdrop-blur-sm border border-gray-100 shadow-sm group-hover:shadow-xl transition-all duration-500">
                        <div class="w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-blue-100 to-white p-3.5 shadow-lg group-hover:shadow-xl transition-shadow">
                            <svg class="w-full h-full text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Découvrez</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Parcourez notre collection exclusive d'espaces premium, filtrés selon vos besoins spécifiques
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Étape 2 -->
            <div class="process-step text-center group" style="animation-delay: 0.2s">
                <div class="step-number group-hover:shadow-xl transition-all duration-500">
                    2
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative p-7 rounded-2xl bg-white/90 backdrop-blur-sm border border-gray-100 shadow-sm group-hover:shadow-xl transition-all duration-500">
                        <div class="w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-blue-100 to-white p-3.5 shadow-lg group-hover:shadow-xl transition-shadow">
                            <svg class="w-full h-full text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 11-4 0 2 2 0 014 0zM9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Sélectionnez</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Choisissez vos dates et options, visualisez la disponibilité en temps réel
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Étape 3 -->
            <div class="process-step text-center group" style="animation-delay: 0.4s">
                <div class="step-number group-hover:shadow-xl transition-all duration-500">
                    3
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative p-7 rounded-2xl bg-white/90 backdrop-blur-sm border border-gray-100 shadow-sm group-hover:shadow-xl transition-all duration-500">
                        <div class="w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-blue-100 to-white p-3.5 shadow-lg group-hover:shadow-xl transition-shadow">
                            <svg class="w-full h-full text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Confirmez</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Validez en quelques clics et recevez votre confirmation immédiate par email
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nos salles -->
<section id="salles" class="py-20 relative overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block text-blue-600 font-semibold text-sm uppercase tracking-wider mb-3">NOS ESPACES PREMIUM</span>
            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                Des lieux qui <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">inspirent</span>
            </h2>
            <div class="section-title"></div>
            <p class="text-gray-600 max-w-2xl mx-auto text-lg">Chaque espace est pensé pour optimiser la productivité et stimuler la créativité</p>
        </div>

        <?php if ($rooms_error): ?>
            <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Impossible de charger les salles</h3>
                <p class="text-gray-600 mb-0"><?php echo htmlspecialchars($rooms_error); ?></p>
            </div>
        <?php elseif (!isset($rooms) || count($rooms) === 0): ?>
            <div class="max-w-2xl mx-auto text-center bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Aucune salle disponible</h3>
                <p class="text-gray-600 mb-0">Ajoutez des salles depuis l'espace admin pour commencer.</p>
            </div>
        <?php else: ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
            <?php foreach ($rooms as $index => $room): ?>
                <div class="room-card hover-lift shadow-lg hover:shadow-2xl group"
                     style="animation-delay: <?php echo $index * 0.1; ?>s">
                    <a href="room.php?id=<?php echo (int)$room['id']; ?>" class="block relative h-64 overflow-hidden rounded-t-xl">
                        <div class="image-loading"></div>
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($room['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($room['name']); ?>" 
                                 class="w-full h-full object-cover"
                                 loading="lazy"
                                 onload="this.classList.add('loaded')">
                        </div>
                        <!-- Badge de prix -->
                        <div class="absolute top-4 right-4">
                            <span class="bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold px-4 py-2 rounded-full shadow-lg transform group-hover:scale-110 transition-transform">
                                <?php echo number_format($room['price'], 2, ',', ' '); ?> €
                            </span>
                        </div>
                        <!-- Overlay au survol -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-end p-6">
                            <div class="text-white transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <?php foreach (array_slice($room['features'], 0, 3) as $feature): ?>
                                        <span class="feature-badge"><?php echo $feature; ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($room['features']) > 3): ?>
                                        <span class="feature-badge">+<?php echo count($room['features']) - 3; ?> autres</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                                    </svg>
                                    <span>Jusqu'à <?php echo min($room['capacity'], 40); ?> personnes</span>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <div class="p-6 flex flex-col flex-grow">
                        <div class="mb-4">
                            <a href="room.php?id=<?php echo (int)$room['id']; ?>" class="block">
                                <h3 class="text-gray-900 text-xl font-bold mb-3 group-hover:text-blue-600 transition-colors">
                                    <?php echo htmlspecialchars($room['name']); ?>
                                </h3>
                            </a>
                            <p class="text-gray-600 leading-relaxed line-clamp-3">
                                <?php echo htmlspecialchars($room['description']); ?>
                            </p>
                        </div>
                        
                        <div class="mt-auto pt-4">
                            <a href="room.php?id=<?php echo (int)$room['id']; ?>&reserve=1" 
                               class="btn-primary w-full text-center flex items-center justify-center group/btn">
                                <svg class="w-5 h-5 mr-3 transform group-hover/btn:rotate-12 transition-transform" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Réserver cet espace
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
        
        <!-- Call to action pour plus d'espaces -->
        <div class="mt-16 text-center animate-fade-in-up" style="animation-delay: 0.6s">
            <a href="#" 
               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg group">
                <svg class="w-5 h-5 ml-3 transform group-hover:translate-x-2 transition-transform" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
                <span>Voir tous nos espaces disponibles</span>
            </a>
        </div>
    </div>
</section>

<!-- Témoignages -->
<section class="py-20 bg-gradient-to-b from-gray-50/50 to-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block text-blue-600 font-semibold mb-3">TÉMOIGNAGES</span>
            <h2 class="text-4xl font-bold mb-4 text-gray-900">Ils nous font <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">confiance</span></h2>
            <div class="section-title"></div>
            <p class="text-gray-600 max-w-2xl mx-auto text-lg">Découvrez les retours d'expérience de nos clients satisfaits</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            $testimonials = [
                [
                    'name' => 'Jean Dupont',
                    'role' => 'Directeur Marketing',
                    'image' => 'https://randomuser.me/api/portraits/men/32.jpg',
                    'content' => '"Service exceptionnel et salles parfaitement équipées. Notre équipe est ravie de l\'expérience professionnelle."',
                    'rating' => 5
                ],
                [
                    'name' => 'Marie Simon',
                    'role' => 'Responsable RH',
                    'image' => 'https://randomuser.me/api/portraits/women/44.jpg',
                    'content' => '"L\'idéal pour nos formations. L\'équipe est très réactive et les espaces sont toujours impeccables."',
                    'rating' => 5
                ],
                [
                    'name' => 'Thomas Martin',
                    'role' => 'CEO, Startup Tech',
                    'image' => 'https://randomuser.me/api/portraits/men/75.jpg',
                    'content' => '"Nous organisons toutes nos réunions importantes ici. Un service irréprochable et des installations haut de gamme."',
                    'rating' => 5
                ]
            ];
            
            foreach ($testimonials as $index => $testimonial):
            ?>
            <div class="testimonial-card group hover-lift" style="animation-delay: <?php echo $index * 0.2; ?>s">
                <div class="flex items-start mb-6">
                    <div class="relative">
                        <img src="<?php echo $testimonial['image']; ?>" 
                             alt="<?php echo $testimonial['name']; ?>" 
                             class="w-14 h-14 rounded-full object-cover border-4 border-white shadow-lg">
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-900"><?php echo $testimonial['name']; ?></h4>
                        <p class="text-sm text-blue-600"><?php echo $testimonial['role']; ?></p>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4 leading-relaxed"><?php echo $testimonial['content']; ?></p>
                <div class="flex text-yellow-400 mb-4">
                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2v2.45M15.89 8.459l-2.36-1.84a1 1 0 00-1.46-.757l-.94-2A2 2 0 0013.33 4h8a2 2 0 001.72.538l.2 1.774a.25.25 0 00.244.085V17a2 2 0 01-2 2H5a2 2 0 01-2-2V6.525a.25.25 0 00-.244-.085l-.2-1.774a1.5 1.5 0 00-1.483-.457Z"></path>
                        </svg>
                    <?php endfor; ?>
                </div>
                <div class="text-xs text-gray-400">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    Il y a 2 semaines
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="relative gradient-bg text-white py-20 overflow-hidden">
    <!-- Éléments décoratifs -->
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full transform translate-x-1/3 -translate-y-1/3"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/5 rounded-full transform -translate-x-1/3 translate-y-1/3"></div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-white/5 rounded-full transform -translate-x-1/2 -translate-y-1/2"></div>
    </div>
    
    <div class="container mx-auto px-4 text-center relative z-10">
        <div class="max-w-3xl mx-auto">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/10 backdrop-blur-sm mb-8 border border-white/20 animate-pulse-slow">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Prêt à transformer vos réunions ?</h2>
            <p class="text-xl text-white/90 mb-10 leading-relaxed">
                Rejoignez des milliers de professionnels qui ont choisi l'excellence pour leurs événements d'entreprise
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-6">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'); ?>" 
                       class="btn-primary text-white font-semibold text-lg inline-flex items-center justify-center group">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Voir mes réservations</span>
                    </a>
                <?php else: ?>
                    <a href="register.php" 
                       class="btn-primary text-white font-semibold text-lg inline-flex items-center justify-center group">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        <span>Commencer gratuitement</span>
                    </a>
                    <a href="login.php" 
                       class="bg-white/10 backdrop-blur-sm border-2 border-white/20 text-white hover:bg-white/20 font-semibold py-4 px-8 rounded-lg text-lg transition-all duration-300 hover:border-white/40 group">
                        <span>Se connecter</span>
                        <svg class="w-5 h-5 ml-3 inline transform group-hover:translate-x-1 transition-transform" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Bénéfices -->
            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6 text-left">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm">Réservation 24/7</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm">Support premium</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm">Annulation flexible</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm">Satisfaction garantie</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Animations au scroll
document.addEventListener('DOMContentLoaded', function() {
    // Observer pour les animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observer les éléments avec animation
    document.querySelectorAll('.animate-fade-in-up, .process-step, .room-card, .testimonial-card').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });

    // Effet parallax sur l'hero
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero-overlay');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Effet de progression sur les cartes
    document.querySelectorAll('.room-card').forEach(card => {
        card.addEventListener('mouseenter', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.style.setProperty('--mouse-x', `${x}px`);
            this.style.setProperty('--mouse-y', `${y}px`);
        });
    });

    // Préchargement progressif des images
    const images = document.querySelectorAll('img[loading="lazy"]');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>