<div align="center">
  <h1>📅 Système de Réservation de Salles de Réunion</h1>
  
  [![PHP Version](https://img.shields.io/badge/PHP-7.0%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0+-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
  [![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
  
  Application web complète pour la gestion et la réservation de salles de réunion avec interface administrateur et utilisateur.
</div>

## ✨ Fonctionnalités

### 👥 Pour les utilisateurs
- 🔐 Création de compte et authentification sécurisée
- 🔍 Consultation des salles disponibles avec filtres
- 📅 Réservation de salles en temps réel
- ✏️ Modification/Annulation de réservations
- 📊 Visualisation du planning des réservations
- 📱 Interface responsive adaptée à tous les appareils

### ⚙️ Pour les administrateurs
- 🏢 Gestion complète des salles (CRUD)
- 👥 Gestion des utilisateurs et des rôles
- 📋 Tableau de bord administratif
- 📝 Consultation et gestion de toutes les réservations
- 📊 Statistiques d'utilisation

## 🚀 Installation rapide

```bash
# 1. Cloner le dépôt
git clone [URL_DU_REPO] salle_reservation
cd salle_reservation

# 2. Configurer la base de données
# - Créer une base MySQL nommée 'salle_reservation'
# - Importer le fichier database/schema.sql

# 3. Configurer les accès BDD
cp config/db.example.php config/db.php
# Puis éditer le fichier avec vos identifiants

# 4. Lancer le serveur de développement (PHP 7.0+ requis)
php -S localhost:8000
```

## 🔧 Configuration requise

- PHP 7.0 ou supérieur
- MySQL 5.7+ ou MariaDB 10.2+
- Serveur web (Apache/Nginx) avec mod_rewrite activé
- Extensions PHP requises : PDO, pdo_mysql, mbstring

## 📁 Structure du projet

```
salle_reservation/
├── assets/               # Fichiers statiques (CSS, JS, images)
│   ├── css/              # Feuilles de style
│   ├── js/               # Scripts JavaScript
│   └── img/              # Images et icônes
│
├── config/               # Fichiers de configuration
│   ├── db.php            # Configuration de la base de données
│   └── config.php        # Configuration générale
│
├── database/             # Schéma et migrations
│   └── schema.sql        # Structure de la base de données
│
├── includes/             # Fonctions et classes utilitaires
│   ├── functions.php     # Fonctions globales
│   └── auth.php          # Gestion de l'authentification
│
├── admin/                # Espace administrateur
│   ├── dashboard.php     # Tableau de bord
│   ├── rooms/            # Gestion des salles
│   ├── users/            # Gestion des utilisateurs
│   └── reservations/     # Gestion des réservations
│
├── user/                 # Espace utilisateur
│   ├── dashboard.php     # Tableau de bord utilisateur
│   ├── reserve.php       # Formulaire de réservation
│   └── profile.php       # Profil utilisateur
│
├── api/                  # Points d'API
├── vendor/               # Dépendances (composer)
├── index.php             # Point d'entrée
└── .htaccess            # Configuration Apache
```

## 🔐 Comptes par défaut

| Rôle | Identifiant | Mot de passe |
|------|-------------|--------------|
| Admin | admin | admin123 |
| Utilisateur | user | user123 |

> **Note** : Changez ces identifiants après la première connexion pour des raisons de sécurité.

## 🛠 Technologies utilisées

- **Backend**
  - PHP 7.0+
  - MySQL/MariaDB
  - PDO (PHP Data Objects)
  - Architecture MVC

- **Frontend**
  - HTML5, CSS3, JavaScript
  - Bootstrap 5.1
  - Font Awesome 6.0
  - jQuery 3.6

- **Outils**
  - Composer (gestion des dépendances)
  - Git (contrôle de version)
  - PHPUnit (tests unitaires)

## 📊 Structure de la base de données

### Table `users`
| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Clé primaire |
| username | VARCHAR(50) | Nom d'utilisateur (unique) |
| email | VARCHAR(100) | Email (unique) |
| password | VARCHAR(255) | Mot de passe hashé |
| role | ENUM('admin','user') | Rôle de l'utilisateur |
| created_at | TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | Dernière mise à jour |

### Table `rooms`
| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Clé primaire |
| name | VARCHAR(100) | Nom de la salle |
| capacity | INT | Capacité maximale |
| equipment | TEXT | Équipements disponibles |
| status | ENUM('active','maintenance') | Statut de la salle |
| created_at | TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | Dernière mise à jour |

### Table `reservations`
| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Clé primaire |
| user_id | INT | Référence à l'utilisateur |
| room_id | INT | Référence à la salle |
| reservation_date | DATE | Date de la réservation |
| start_time | TIME | Heure de début |
| end_time | TIME | Heure de fin |
| purpose | VARCHAR(255) | Objet de la réunion |
| status | ENUM('pending','confirmed','cancelled') | Statut |
| created_at | TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | Dernière mise à jour |

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou à soumettre une pull request.

1. Fork le projet
2. Créez votre branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Poussez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request