# SpaceX Launch Dashboard - Backend API

## 📋 Description

Backend API REST développé avec Laravel pour le tableau de bord des lancements SpaceX. Cette API fournit tous les services nécessaires pour gérer les données de lancements, l'authentification des utilisateurs et les fonctionnalités d'administration.

## 🚀 Technologies

- **Framework** : Laravel 12.x
- **PHP** : ≥ 8.2
- **Base de données** : MySQL / MariaDB
- **Authentification** : JWT (JSON Web Tokens)
- **Documentation API** : Scramble (Auto-générée)
- **Cache** : Redis (optionnel)

## 📦 Fonctionnalités

### 🔐 Authentification
- Inscription et connexion des utilisateurs
- Authentification JWT avec tokens sécurisés
- Gestion des rôles (USER, ADMIN)
- Middleware de protection des routes

### 🚀 Gestion des Lancements
- **API SpaceX Integration** : Synchronisation automatique avec l'API officielle SpaceX
- **CRUD Complet** : Création, lecture, mise à jour et suppression des lancements
- **Filtrage Avancé** : Par année, statut, fusée, succès/échec
- **Recherche** : Recherche textuelle dans les noms et descriptions
- **Pagination** : Pagination optimisée pour les grandes listes

### 📊 Dashboard & Analytics
- **KPIs en temps réel** : Total des lancements, taux de succès, prochains lancements
- **Statistiques** : Données pour graphiques (lancements par année, taux de succès)
- **Prochain lancement** : Informations détaillées avec countdown

### ⚡ Administration
- **Synchronisation SpaceX** : Mise à jour automatique des données depuis l'API SpaceX
- **Gestion des utilisateurs** : Administration des comptes utilisateurs
- **Monitoring** : Logs et surveillance des performances

## 🛠 Installation

### Prérequis
```bash
- PHP 8.2 ou supérieur
- Composer
- MySQL ou MariaDB
- Node.js (pour les assets frontend si applicable)
```

### 1. Clonage et installation des dépendances
```bash
git clone <repository-url>
cd Backend-SpaceX-Launch-Dashboard/laravel
composer install
```

### 2. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configuration de la base de données
Modifiez le fichier `.env` avec vos paramètres de base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spacex_dashboard
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Configuration JWT
```bash
php artisan jwt:secret
```

### 5. Migration et seeding
```bash
php artisan migrate
php artisan db:seed
```

### 6. Démarrage du serveur
```bash
php artisan serve
```

L'API sera accessible sur `http://localhost:8000`

## 📚 Endpoints API

### 🔐 Authentification
```http
POST   /api/auth/register          # Inscription
POST   /api/auth/login             # Connexion  
POST   /api/auth/logout            # Déconnexion
POST   /api/auth/refresh           # Rafraîchissement du token
GET    /api/auth/user              # Profil utilisateur
```

### 🚀 Lancements
```http
GET    /api/launches               # Liste des lancements (avec filtres)
GET    /api/launches/{id}          # Détails d'un lancement
GET    /api/launches/years         # Années disponibles
POST   /api/launches               # Créer un lancement (Admin)
PUT    /api/launches/{id}          # Modifier un lancement (Admin)
DELETE /api/launches/{id}          # Supprimer un lancement (Admin)
```

### 📊 Dashboard
```http
GET    /api/dashboard/kpis         # Indicateurs clés
GET    /api/dashboard/charts       # Données pour graphiques
```

### ⚡ Administration
```http
POST   /api/admin/sync             # Synchronisation SpaceX (Admin uniquement)
GET    /api/admin/users            # Liste des utilisateurs (Admin)
```

### 🏥 Monitoring
```http
GET    /api/health                 # Statut de l'API
```

## 📊 Paramètres de filtrage

### Lancements (`/api/launches`)
```http
?search=falcon                     # Recherche textuelle
?year=2024                         # Filtrer par année
?success=true                      # Filtrer par succès (true/false)
?rocket=Falcon%209                 # Filtrer par fusée
?status=upcoming                   # Filtrer par statut
?page=1                           # Page (défaut: 1)
?per_page=10                      # Éléments par page (défaut: 20)
```

## 🏗 Architecture

### Structure des dossiers
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AdminController.php
│   │   │   ├── DashboardController.php
│   │   │   └── LaunchController.php
│   │   └── Auth/
│   │       ├── LoginController.php
│   │       └── RegisterController.php
│   ├── Middleware/
│   └── Requests/
├── Models/
│   ├── Launch.php
│   └── User.php
└── Services/
    ├── LaunchService.php
    └── SpaceXService.php
```

### Modèles de données

#### Launch Model
```php
- id: bigint (primary)
- spacex_id: string (unique)
- name: string
- rocket: string
- description: text
- date: datetime
- success: boolean
- links: json (videos, images, etc.)
- details: json (payload, orbit, etc.)
- timestamps
```

#### User Model
```php
- id: bigint (primary)
- name: string
- email: string (unique)
- password: string (hashed)
- role: enum (USER, ADMIN)
- timestamps
```

## 🔧 Configuration

### Variables d'environnement importantes
```env
# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spacex_dashboard

# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=60

# SpaceX API
SPACEX_API_URL=https://api.spacexdata.com/v5

# CORS (pour le frontend)
FRONTEND_URL=http://localhost:5173
```

## 🧪 Tests

### Lancer les tests
```bash
php artisan test
```

### Tests disponibles
- Tests d'authentification
- Tests des endpoints API
- Tests de la synchronisation SpaceX
- Tests des filtres et recherche

## 📈 Performance

### Optimisations implémentées
- **Cache** : Mise en cache des KPIs et statistiques
- **Pagination** : Pagination efficace pour les grandes listes
- **Index de base de données** : Index optimisés pour les recherches
- **Lazy loading** : Chargement paresseux des relations

### Monitoring
- Logs structurés avec Laravel Pail
- Métriques de performance
- Health check endpoint

## 🛡 Sécurité

### Mesures de sécurité
- **JWT Authentication** : Tokens sécurisés avec expiration
- **CORS** : Configuration CORS appropriée
- **Validation** : Validation stricte des entrées
- **Rate Limiting** : Limitation du taux de requêtes
- **Hash des mots de passe** : Bcrypt pour les mots de passe

## 🚀 Déploiement

### Production
```bash
# Optimisation pour la production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migration en production
php artisan migrate --force
```

### Variables d'environnement de production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Base de données de production
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=spacex_dashboard_prod
```

## 📖 Documentation API avec Scramble

### 🚀 Qu'est-ce que Scramble ?

**Scramble** est un package Laravel qui génère automatiquement une documentation OpenAPI/Swagger complète pour votre API. Il analyse votre code Laravel et crée une documentation interactive en temps réel.

### ✨ Fonctionnalités de Scramble

- **Génération Automatique** : Analyse automatique des routes, contrôleurs et modèles
- **Documentation Interactive** : Interface Swagger UI pour tester les endpoints
- **Types TypeScript** : Génération automatique des types pour le frontend
- **Validation en Temps Réel** : Documentation des règles de validation Laravel
- **Authentification JWT** : Support complet de l'authentification par tokens
- **Exemples Automatiques** : Génération d'exemples de requêtes/réponses

### 📍 Accès à la Documentation

La documentation complète de l'API est accessible à :
```
http://localhost:8000/docs/api
```

### 🔧 Configuration Scramble

Dans `config/scramble.php` :
```php
'api_path' => 'api',
'api_domain' => null,
'info' => [
    'title' => 'SpaceX Launch Dashboard API',
    'description' => 'API REST pour le tableau de bord des lancements SpaceX',
    'version' => '1.0.0',
],
```

### 📊 Fonctionnalités Avancées

#### 1. Types TypeScript Automatiques
```bash
# Générer les types TypeScript pour le frontend
php artisan scramble:export --format=typescript
```

#### 2. Documentation des Modèles
Scramble détecte automatiquement :
- Les relations Eloquent
- Les attributs de modèle
- Les règles de validation
- Les transformations de données

#### 3. Exemples de Réponses
```php
/**
 * @response 200 {
 *   "success": true,
 *   "data": {
 *     "launches": [...],
 *     "pagination": {...}
 *   }
 * }
 */
public function index(Request $request)
```

#### 4. Documentation des Erreurs
```php
/**
 * @response 401 {
 *   "success": false,
 *   "message": "Non autorisé"
 * }
 * @response 422 {
 *   "success": false,
 *   "message": "Données invalides",
 *   "errors": {...}
 * }
 */
```

### 🎯 Utilisation Pratique

1. **Développement Frontend** : Consultez la doc pour connaître les endpoints
2. **Tests API** : Testez directement depuis l'interface Swagger
3. **Intégration** : Exportez les types TypeScript pour le frontend
4. **Débogage** : Visualisez la structure des réponses JSON

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/amazing-feature`)
3. Commit les changements (`git commit -m 'Add amazing feature'`)
4. Push sur la branche (`git push origin feature/amazing-feature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👥 Auteurs

- **Mozaire** - Développeur principal

## 📞 Support

Pour toute question ou problème :
- Ouvrir une issue sur GitHub
- Email : [kmozaire83@gmail.com]

---

