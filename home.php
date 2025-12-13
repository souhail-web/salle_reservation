<?php
$pageTitle = 'Accueil';
require_once 'config/db.php';
require_once 'includes/header.php';

// Exemple de données de salles (à remplacer par une requête à la base de données)
$rooms = [
    [
        'id' => 1,
        'name' => 'Salle de conférence VIP',
        'description' => 'Espace haut de gamme avec écran tactile 4K, système audio professionnel et sièges en cuir pour des réunions d\'exception.',
        'price' => 299.99,
        'capacity' => 20,
        'image' => 'assets/images/a.jfif',
        'features' => ['Écran tactile 4K', 'Système audio premium', 'Climatisation silencieuse', 'Wifi fibre']
    ],
    [
        'id' => 2,
        'name' => 'Salle de réunion standard',
        'description' => 'Espace fonctionnel et modulable, idéal pour les réunions d\'équipe et les ateliers collaboratifs quotidiens.',
        'price' => 149.99,
        'capacity' => 10,
        'image' => 'assets/images/bjfif.jfif',
        'features' => ['Écran 65\"', 'Tableau blanc interactif', 'Climatisation', 'Wifi']
    ],
    [
        'id' => 3,
        'name' => 'Espace créatif',
        'description' => 'Environnement lumineux et inspirant avec mobilier design pour stimuler la créativité lors de vos ateliers.',
        'price' => 199.99,
        'capacity' => 15,
        'image' => 'assets/images/c.jfif',
        'features' => ['Murs effaçables', 'Mobilier design', 'Éclairage LED', 'Wifi']
    ],
    [
        'id' => 4,
        'name' => 'Salle de formation',
        'description' => 'Espace éducatif équipé pour des formations professionnelles avec des équipements pédagogiques de pointe.',
        'price' => 179.99,
        'capacity' => 25,
        'image' => 'assets/images/d.jfif',
        'features' => ['Écran de projection', 'Tables individuelles', 'Climatisation', 'Wifi']
    ],
    [
        'id' => 5,
        'name' => 'Salle de conférence panoramique',
        'description' => 'Vue imprenable avec une baie vitrée, parfaite pour impressionner vos clients lors de réunions importantes.',
        'price' => 349.99,
        'capacity' => 30,
        'image' => 'assets/images/e.jfif',
        'features' => ['Vue panoramique', 'Système vidéo 4K', 'Service traiteur', 'Wifi']
    ],
    [
        'id' => 6,
        'name' => 'Espace coworking',
        'description' => 'Espace de travail partagé avec postes de travail ergonomiques et zones de détente pour plus de convivialité.',
        'price' => 99.99,
        'capacity' => 40,
        'image' => 'assets/images/fjfif.jfif',
        'features' => ['Bureaux partagés', 'Espace détente', 'Café illimité', 'Wifi']
    ]
];
?>

<!-- Bannière principale avec image de fond -->
<section class="relative bg-gray-900 text-white overflow-hidden">
    <!-- Image de fond avec superposition sombre -->
    <div class="absolute inset-0 z-0">
        <img src="assets/images/a.jfif" 
             alt="Salle de conférence élégante" 
             class="w-full h-full object-cover opacity-50">
    </div>
    
    <div class="container mx-auto px-4 py-24 md:py-32 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">Trouvez l'espace idéal pour vos événements professionnels</h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-10 max-w-3xl mx-auto">Des salles équipées et design pour vos réunions, formations et séminaires d'entreprise</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#salles" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    Découvrir nos espaces
                </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="bg-transparent border-2 border-white text-white hover:bg-white hover:bg-opacity-10 font-bold py-3 px-6 rounded-lg text-lg transition duration-300">
                Créer un compte
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Comment ça marche -->
<section id="how-it-works" class="py-24 bg-white">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-20" data-aos="fade-up">
            <span class="inline-block text-blue-600 text-sm font-medium tracking-widest uppercase mb-4">Notre Processus</span>
            <h2 class="text-4xl font-bold text-gray-900 mb-5">Comment réserver votre espace</h2>
            <div class="w-16 h-1 bg-gradient-to-r from-blue-500 to-blue-300 mx-auto mb-6"></div>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">Découvrez notre processus de réservation simple et efficace en trois étapes claires.</p>
        </div>
        
        <div class="relative">
            <!-- Ligne de connexion discrète -->
            <div class="hidden md:block absolute left-1/2 top-1/2 w-2/3 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent transform -translate-x-1/2 -translate-y-1/2"></div>
            
            <div class="relative z-10 grid md:grid-cols-3 gap-16">
                <!-- Étape 1 -->
                <div class="text-center group" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative w-20 h-20 mx-auto mb-8">
                        <div class="absolute inset-0 bg-white border-2 border-blue-100 rounded-full transform transition-all duration-300 group-hover:border-blue-500">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="relative w-full h-full flex items-center justify-center">
                            <span class="text-2xl font-semibold text-blue-600 transition-colors duration-300 group-hover:text-blue-700">1</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Trouvez</h3>
                    <p class="text-gray-600 leading-relaxed px-2">Parcourez notre collection exclusive d'espaces de travail soigneusement sélectionnés.</p>
                </div>
                
                <!-- Étape 2 -->
                <div class="text-center group" data-aos="fade-up" data-aos-delay="200">
                    <div class="relative w-20 h-20 mx-auto mb-8">
                        <div class="absolute inset-0 bg-white border-2 border-blue-100 rounded-full transform transition-all duration-300 group-hover:border-blue-500">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="relative w-full h-full flex items-center justify-center">
                            <span class="text-2xl font-semibold text-blue-600 transition-colors duration-300 group-hover:text-blue-700">2</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Choisissez</h3>
                    <p class="text-gray-600 leading-relaxed px-2">Sélectionnez les dates et options qui correspondent à vos besoins spécifiques.</p>
                </div>
                
                <!-- Étape 3 -->
                <div class="text-center group" data-aos="fade-up" data-aos-delay="300">
                    <div class="relative w-20 h-20 mx-auto mb-8">
                        <div class="absolute inset-0 bg-white border-2 border-blue-100 rounded-full transform transition-all duration-300 group-hover:border-blue-500">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="relative w-full h-full flex items-center justify-center">
                            <span class="text-2xl font-semibold text-blue-600 transition-colors duration-300 group-hover:text-blue-700">3</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Confirmez</h3>
                    <p class="text-gray-600 leading-relaxed px-2">Finalisez en quelques secondes et recevez votre confirmation immédiatement.</p>
                </div>
            </div>
        </div>
        
        <div class="mt-20 text-center" data-aos="fade-up" data-aos-delay="400">
            <a href="#salles" class="inline-flex items-center px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-300 border border-transparent hover:shadow-lg transform hover:-translate-y-0.5">
                <span>Explorer nos espaces</span>
                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- Nos salles -->
<section id="salles" class="py-20 bg-gradient-to-b from-white to-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block text-blue-600 font-semibold mb-3">NOS ESPACES</span>
            <h2 class="text-4xl font-bold mb-4 text-gray-900">Découvrez nos salles premium</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto mb-6"></div>
            <p class="text-gray-600 max-w-2xl mx-auto text-lg">Des espaces de travail conçus pour inspirer la productivité et l'innovation, équipés des dernières technologies.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($rooms as $room): ?>
                <div class="group bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo htmlspecialchars($room['image']); ?>" 
                             alt="<?php echo htmlspecialchars($room['name']); ?>" 
                             class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                            <div class="text-white">
                                <span class="inline-block bg-blue-600 text-white text-sm font-medium px-3 py-1 rounded-full mb-2">
                                    Jusqu'à <?php echo $room['capacity']; ?> personnes
                                </span>
                                <div class="flex items-center space-x-2">
                                    <?php foreach ($room['features'] as $feature): ?>
                                    <span class="text-xs bg-white/20 backdrop-blur-sm px-2 py-1 rounded-full"><?php echo $feature; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-gray-900 text-xl font-bold"><?php echo htmlspecialchars($room['name']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($room['description']); ?></p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'user/reserve.php?room_id=' . $room['id'] : 'register.php'; ?>" 
                           class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300 transform hover:scale-105">
                            <i class="far fa-calendar-check mr-2"></i>Réserver maintenant
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Témoignages -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block text-blue-600 font-semibold mb-3">TÉMOIGNAGES</span>
            <h2 class="text-4xl font-bold mb-4 text-gray-900">Ils nous font confiance</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto mb-6"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-6">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Jean Dupont" class="w-14 h-14 rounded-full object-cover border-2 border-blue-100">
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-900">Jean Dupont</h4>
                        <p class="text-sm text-blue-600">Directeur Marketing</p>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"Service exceptionnel et salles parfaitement équipées. Notre équipe est ravie de l'expérience."</p>
                <div class="flex text-yellow-400">
                    ★★★★★
                </div>
            </div>
            <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-6">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Marie Simon" class="w-14 h-14 rounded-full object-cover border-2 border-blue-100">
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-900">Marie Simon</h4>
                        <p class="text-sm text-blue-600">Responsable RH</p>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"L'idéal pour nos formations. L'équipe est très réactive et les espaces sont toujours impeccables."</p>
                <div class="flex text-yellow-400">
                    ★★★★★
                </div>
            </div>
            <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center mb-6">
                    <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Thomas Martin" class="w-14 h-14 rounded-full object-cover border-2 border-blue-100">
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-900">Thomas Martin</h4>
                        <p class="text-sm text-blue-600">CEO, Startup Tech</p>
                    </div>
                </div>
                <p class="text-gray-600 italic mb-4">"Nous organisons toutes nos réunions importantes ici. Un service irréprochable et des installations haut de gamme."</p>
                <div class="flex text-yellow-400">
                    ★★★★★
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 mr-3 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Client" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="font-semibold">Julie Leroy</h4>
                        <p class="text-sm text-gray-500">Chef de projet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="relative bg-blue-700 text-white py-20 overflow-hidden">
    <!-- Éléments décoratifs -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-blue-600/20 to-blue-800/20"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-400 rounded-full opacity-10 transform translate-x-32 -translate-y-32"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500 rounded-full opacity-10 transform -translate-x-48 translate-y-48"></div>
    </div>
    
    <div class="container mx-auto px-4 text-center relative z-10">
        <h2 class="text-4xl font-bold mb-6">Prêt à réserver votre espace ?</h2>
        <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">Rejoignez des milliers de professionnels qui nous font confiance pour leurs événements d'entreprise.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-6">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'); ?>" 
                   class="bg-white text-blue-700 hover:bg-blue-50 font-bold py-3 px-6 rounded-lg text-lg transition duration-300 transform hover:scale-105">
                    Accéder à mon espace
                </a>
            <?php else: ?>
                <a href="register.php" 
                   class="bg-white text-blue-700 hover:bg-blue-50 font-bold py-3 px-6 rounded-lg text-lg transition duration-300 transform hover:scale-105">
                    Créer un compte gratuit
                </a>
                <a href="login.php" 
                   class="bg-transparent border-2 border-white text-white hover:bg-white hover:bg-opacity-10 font-bold py-3 px-6 rounded-lg text-lg transition duration-300">
                    Se connecter
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
