    </main>

    <!-- Pied de page -->
    <footer class="bg-gray-800 text-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- À propos -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">À propos</h3>
                    <p class="text-gray-400">
                        Plateforme de réservation de salles en ligne pour vos réunions, séminaires et événements professionnels.
                    </p>
                </div>

                <!-- Liens rapides -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Liens rapides</h3>
                    <ul class="space-y-2">
                        <li><a href="home.php" class="text-gray-400 hover:text-white transition">Accueil</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Nos salles</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition">Comment ça marche</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition">Tarifs</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-2"></i>
                            <span>123 Rue de la Réservation, 75000 Paris</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-2"></i>
                            <a href="tel:+33123456789" class="hover:text-white transition">+33 1 23 45 67 89</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <a href="mailto:contact@reservation-salles.fr" class="hover:text-white transition">contact@reservation-salles.fr</a>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
                    <p class="text-gray-400 mb-3">Inscrivez-vous pour recevoir nos offres spéciales</p>
                    <form class="flex">
                        <input type="email" placeholder="Votre email" class="px-4 py-2 rounded-l-md focus:outline-none text-gray-800 w-full">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md transition">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-12 pt-6 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> Réservation de Salles. Tous droits réservés.</p>
                <div class="mt-2">
                    <a href="#" class="hover:text-white transition">Mentions légales</a>
                    <span class="mx-2">•</span>
                    <a href="#" class="hover:text-white transition">Politique de confidentialité</a>
                    <span class="mx-2">•</span>
                    <a href="#" class="hover:text-white transition">CGU</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Menu mobile
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                // Changer l'icône du bouton
                const icon = mobileMenuButton.querySelector('i');
                if (mobileMenu.classList.contains('hidden')) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
            });
        }

        // Fermer le menu mobile lors du clic sur un lien
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                const icon = mobileMenuButton.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });

        // Ajouter une classe active au lien de la page courante
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = window.location.pathname.split('/').pop() || 'home.php';
            const navLinks = document.querySelectorAll('nav a');
            
            navLinks.forEach(link => {
                const linkPath = link.getAttribute('href');
                if (linkPath === currentPath || 
                    (currentPath === '' && linkPath === 'home.php') ||
                    (currentPath.includes('dashboard.php') && linkPath.includes('dashboard.php'))) {
                    link.classList.add('text-blue-500', 'font-semibold');
                    link.classList.remove('text-gray-700', 'hover:text-blue-600');
                }
            });
        });
    </script>
</body>
</html>
