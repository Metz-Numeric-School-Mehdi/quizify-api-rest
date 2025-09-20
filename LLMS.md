# Quizify API REST - Documentation Complète pour LLM

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture technique](#architecture-technique)
3. [Structure du projet](#structure-du-projet)
4. [Fonctionnalités principales](#fonctionnalités-principales)
5. [Configuration](#configuration)
6. [Installation](#installation)
7. [API/Endpoints](#apiendpoints)
8. [Base de données](#base-de-données)
9. [Tests](#tests)
10. [Déploiement](#déploiement)
11. [Points d'attention](#points-dattention)

## Vue d'ensemble

### Résumé du projet
**Quizify** est une plateforme de quiz interactive complète développée avec Laravel 12. Elle permet aux utilisateurs de créer, partager et participer à des quiz avec un système de scoring avancé, un classement compétitif, un système de badges, une recherche Elasticsearch intégrée et un module de paiement Stripe complet avec trois niveaux d'abonnement.

### Objectifs principaux
- **Création collaborative** : Permettre aux utilisateurs de créer et partager des quiz
- **Compétition sociale** : Système de classement et badges pour encourager l'engagement
- **Flexibilité organisationnelle** : Support des organisations et équipes
- **Performance** : Recherche rapide via Elasticsearch
- **Extensibilité** : Architecture modulaire avec repositories et services
- **Monétisation** : Système d'abonnement Stripe avec limitations par plan

## Architecture technique

### Stack technologique
- **Backend** : PHP 8.2+ avec Laravel 12.x
- **Authentification** : Laravel Sanctum pour l'API
- **Base de données** : MySQL/MariaDB avec migrations Laravel
- **Recherche** : Elasticsearch via Laravel Scout
- **Paiements** : Stripe avec Laravel Cashier
- **Webhooks** : Stripe CLI pour développement local
- **Stockage** : Support AWS S3 via Flysystem
- **Conteneurisation** : Docker avec docker-compose
- **Tests** : Pest (framework de test moderne)
- **Frontend Assets** : Vite avec TailwindCSS

### Dépendances principales
```json
{
  "laravel/framework": "^12.0",
  "laravel/sanctum": "^4.0",
  "laravel/scout": "^10.17",
  "laravel/cashier": "^15.7",
  "laravel/tinker": "^2.10.1",
  "league/flysystem-aws-s3-v3": "^3.0",
  "matchish/laravel-scout-elasticsearch": "^7.11",
  "stripe/stripe-php": "^13.0"
}
```

### Composants découverts
- **Repository Pattern** : Abstraction des données avec `App\Components\Repository`
- **Strategy Pattern** : Stratégies de règles pour questions, quiz et réponses
- **Service Layer** : `ElasticsearchService`, `LeaderboardService`, `SubscriptionService`
- **Resource Layer** : Transformateurs API avec `QuizResource`, `QuestionResource`
- **Exception Handling** : Exceptions personnalisées avec `ApiException`, `QuizException`
- **Stripe Integration** : Module complet avec webhooks automatisés
- **Subscription Management** : Plans d'abonnement avec limitations automatiques

### Flux de données
1. **Authentification** : Sanctum → Middleware auth:sanctum → Controller
2. **CRUD Operations** : Controller → Repository → Model → Database
3. **Search** : Controller → Scout → Elasticsearch → Fallback MySQL
4. **Quiz Submission** : Controller → Repository → Business Logic → Score Calculation
5. **Stripe Payments** : Checkout → Webhook → Plan Update → User Sync
6. **Subscription Limits** : Middleware → Plan Check → Access Control
## Structure du projet

### Arborescence principale
```
quizify-api-rest/
├── app/                             # Code applicatif principal
│   ├── Components/                  # Composants réutilisables
│   │   ├── Repository.php           # Classe de base Repository
│   │   ├── Abstracts/              
│   │   │   └── RuleStrategy.php     # Stratégie abstraite pour règles
│   │   ├── Contexts/               # Contextes (vide actuellement)
│   │   └── Interfaces/             # Interfaces du système
│   │       ├── RepositoryInterface.php
│   │       └── RuleStrategyInterface.php
│   ├── Console/                    # Commandes Artisan
│   │   ├── Kernel.php              # Noyau des commandes
│   │   └── Commands/               
│   │       └── UpdateLeaderboardRanking.php  # Mise à jour classement
│   ├── Enums/                      # Énumérations (vide)
│   ├── Exceptions/                 # Gestion des exceptions
│   │   ├── ApiException.php        # Exception API générique
│   │   ├── Handler.php             # Gestionnaire d'exceptions global
│   │   └── Quiz/
│   │       └── QuizException.php   # Exceptions spécifiques aux quiz
│   ├── Http/                       # Couche HTTP
│   │   ├── Controllers/            # Contrôleurs API
│   │   │   ├── Controller.php      # Contrôleur de base
│   │   │   ├── CRUDController.php  # Contrôleur CRUD générique
│   │   │   ├── AuthController.php  # Authentification
│   │   │   ├── QuizController.php  # Gestion des quiz
│   │   │   ├── QuestionController.php
│   │   │   ├── AnswerController.php
│   │   │   ├── SubscriptionController.php  # Gestion abonnements Stripe
│   │   │   ├── UserController.php
│   │   │   ├── LeaderboardController.php
│   │   │   ├── BadgeController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── TeamController.php
│   │   │   ├── OrganizationController.php
│   │   │   ├── ScoreController.php
│   │   │   ├── QuestionTypeController.php
│   │   │   ├── QuestionResponseController.php
│   │   │   └── QuizLevelController.php
│   │   ├── Middleware/              # Middlewares personnalisés
│   │   │   ├── Authenticate.php
│   │   │   ├── EncryptCookies.php
│   │   │   └── VerifyCsrfToken.php
│   │   ├── Modules/                 # Modules métier
│   │   │   ├── Questions/Strategies/
│   │   │   │   └── QuestionRuleStrategy.php
│   │   │   ├── Quizzes/Strategies/
│   │   │   │   └── QuizRuleStrategy.php
│   │   │   └── Answers/Strategies/
│   │   │       └── AnswerRuleStrategy.php
│   │   ├── Requests/               # Validation des requêtes
│   │   │   └── StoreEntityRequest.php
│   │   └── Resources/              # Transformateurs de données
│   │       ├── QuestionResource.php
│   │       └── QuizResource.php
│   ├── Models/                     # Modèles Eloquent
│   │   ├── User.php               # Modèle utilisateur avec relations
│   │   ├── Quiz.php               # Modèle quiz avec Elasticsearch
│   │   ├── Question.php           # Questions de quiz
│   │   ├── Answer.php             # Réponses aux questions
│   │   ├── SubscriptionPlan.php   # Plans d'abonnement Stripe
│   │   ├── QuestionType.php       # Types de questions
│   │   ├── QuestionResponse.php   # Réponses utilisateurs
│   │   ├── QuizAttempt.php        # Tentatives de quiz
│   │   ├── Score.php              # Scores utilisateurs
│   │   ├── Badge.php              # Système de badges
│   │   ├── UserBadge.php          # Association user-badge
│   │   ├── Organization.php       # Organisations
│   │   ├── Team.php               # Équipes
│   │   ├── Role.php               # Rôles utilisateurs
│   │   ├── Category.php           # Catégories de quiz
│   │   ├── QuizLevel.php          # Niveaux de difficulté
│   │   ├── Tag.php                # Tags pour quiz
│   │   ├── QuizSchedule.php       # Planification quiz
│   │   └── ExportImport.php       # Import/export données
│   ├── Providers/                 # Service Providers
│   │   └── AppServiceProvider.php # Configuration services
│   ├── Repositories/              # Couche Repository
│   │   ├── Quiz/
│   │   │   └── QuizRepository.php # Repository quiz
│   │   ├── Question/
│   │   │   └── QuestionRepository.php
│   │   └── Answer/
│   │       └── AnswerRepository.php
│   └── Services/                  # Services métier
│       ├── ElasticsearchService.php # Service Elasticsearch
│       ├── LeaderboardService.php   # Service classement
│       └── SubscriptionService.php  # Service abonnements Stripe
├── config/                        # Configuration Laravel
│   ├── app.php                    # Configuration application
│   ├── auth.php                   # Configuration authentification
│   ├── database.php               # Configuration base de données
│   ├── elasticsearch.php          # Configuration Elasticsearch
│   ├── cors.php                   # Configuration CORS
│   ├── sanctum.php                # Configuration Sanctum
│   └── scout.php                  # Configuration Laravel Scout
├── database/                      # Base de données
│   ├── factories/                 # Factories pour tests
│   ├── migrations/                # Migrations base de données
│   └── seeders/                   # Seeders pour données
├── routes/                        # Définition des routes
│   ├── api.php                    # Routes API
│   ├── auth.php                   # Routes authentification
│   ├── web.php                    # Routes web
│   └── console.php                # Routes console
├── tests/                         # Tests automatisés
│   ├── Unit/                      # Tests unitaires
│   └── Feature/                   # Tests d'intégration
├── docs/                          # Documentation
│   ├── db/MySqlArchitecture.md    # Architecture base de données
│   ├── postman/                   # Collection Postman
│   ├── cr/                        # Comptes rendus
│   └── rapport/                   # Rapports
├── docker-compose.yml             # Configuration Docker
├── Dockerfile                     # Image Docker
├── Makefile                       # Commandes make utiles
└── .github/workflows/main.yml     # CI/CD GitHub Actions
```

### Dossiers et fichiers clés

- **`app/Components/`** : Architecture en composants réutilisables avec pattern Repository et Strategy
- **`app/Http/Controllers/`** : Contrôleurs REST avec CRUD générique et spécialisations
- **`app/Models/`** : Modèles Eloquent avec relations complexes et soft deletes
- **`app/Repositories/`** : Couche d'abstraction base de données suivant le pattern Repository
- **`app/Services/`** : Services métier pour logique complexe (Elasticsearch, Leaderboard)
- **`database/migrations/`** : 23+ migrations définissant le schéma complet
- **`routes/api.php`** : API REST complète avec authentification Sanctum
- **`tests/`** : Tests avec Pest framework (Unit + Feature)
- **`docker-compose.yml`** : Environnement complet avec MySQL, Elasticsearch, MinIO

## Fonctionnalités principales

### Système d'authentification complet
- **Registration/Login** : Via `AuthController` avec validation
- **API Authentication** : Laravel Sanctum avec tokens
- **Role-based Access** : Système de rôles avec middleware
- **Password Security** : Hachage Bcrypt avec règles complexes

### Module Stripe d'abonnement complet
- **Plans d'abonnement** : Free (0€), Premium (9.99€), Business (29.99€)
- **Paiements sécurisés** : Checkout Sessions Stripe avec webhooks
- **Limitations automatiques** : Middleware de contrôle d'accès par plan
- **Synchronisation automatique** : Webhooks pour mise à jour des plans
- **Tests locaux** : Stripe CLI pour développement
- **Gestion d'erreurs** : Exceptions spécialisées et logging complet

### 📝 Gestion avancée des quiz
- **CRUD Quiz** : Création, lecture, mise à jour, suppression
- **Types de questions multiples** : QCM, texte libre, etc.
- **Système de tags** : Catégorisation et organisation
- **Niveaux de difficulté** : Classification par niveau
- **Publication/Brouillon** : Statuts de publication
- **Soft Delete** : Suppression logique avec récupération

### 🔍 Recherche intelligente
- **Elasticsearch** : Recherche full-text performante via Scout
- **Fallback MySQL** : Basculement automatique si Elasticsearch indisponible
- **Filtres avancés** : Par catégorie, niveau, statut, visibilité
- **Pagination** : Gestion des résultats paginés

### 🏆 Système de scoring et classement
- **Calcul automatique** : Scores basés sur réponses correctes
- **Classement global** : Ranking automatique des utilisateurs
- **Classement par catégorie** : Leaderboards spécialisés
- **Classement organisationnel** : Par organisation/équipe
- **Badges d'achievement** : Système de récompenses

### 👥 Gestion organisationnelle
- **Organisations** : Structures hiérarchiques
- **Équipes** : Groupes au sein d'organisations
- **Rôles utilisateurs** : Permissions différenciées
- **Profile utilisateur** : Photos, informations personnelles

### 📊 Analytics et suivi
- **Quiz Attempts** : Historique des tentatives
- **Question Responses** : Détail des réponses
- **Progress Tracking** : Suivi progression utilisateur
- **Export/Import** : Fonctionnalités d'import/export

## Configuration

### Variables d'environnement principales
```bash
# Application
APP_NAME=Quizify
APP_ENV=local
APP_URL=http://localhost
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quizify
DB_USERNAME=root
DB_PASSWORD=root

# Elasticsearch
SCOUT_DRIVER=elasticsearch
ELASTICSEARCH_HOST=localhost:9200
ELASTICSEARCH_INDEX=quizzes

# Stripe (optionnel pour paiements)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Stripe CLI pour développement local
# Installer : https://stripe.com/docs/stripe-cli
# Démarrer : stripe listen --forward-to localhost:8000/api/webhook/stripe

# Admin par défaut
ADMIN_USERNAME=
ADMIN_FIRSTNAME=
ADMIN_LASTNAME=
ADMIN_EMAIL=
ADMIN_PASSWORD=

# MinIO (stockage local)
MINIO_ROOT_USER=
MINIO_ROOT_PASSWORD=
MINIO_ENDPOINT=
MINIO_ACCESS_KEY=
MINIO_SECRET_KEY=
MINIO_BUCKET=
```

### Fichiers de configuration Laravel
- **`config/elasticsearch.php`** : Configuration Elasticsearch dédiée
- **`config/scout.php`** : Configuration Laravel Scout
- **`config/sanctum.php`** : Configuration authentification API
- **`config/cors.php`** : Configuration CORS pour SPA
- **`config/filesystems.php`** : Configuration stockage (S3/MinIO)

## Installation

### Prérequis
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Elasticsearch (optionnel, fallback MySQL)
- Docker & Docker Compose (recommandé)

### Installation avec Docker (Recommandée)
```bash
# 1. Cloner le repository
git clone <repository-url>
cd quizify-api-rest

# 2. Copier la configuration
cp .env.example .env

# 3. Build et démarrage avec base fraîche
make up-fresh

# 4. L'application est accessible sur http://localhost:8000
```

### Installation manuelle
```bash
# 1. Installation des dépendances
composer install

# 2. Configuration
cp .env.example .env
php artisan key:generate

# 3. Base de données
php artisan migrate:fresh --seed

# 4. Démarrage
php artisan serve
```

### Commandes Makefile disponibles
```bash
make build        # Build de l'image Docker
make up           # Démarrage avec migrations
make up-fresh     # Démarrage avec base fraîche
make fresh-seed   # Migration fresh + seed
make adminer      # Interface web base de données
make down         # Arrêt des services
make clear-all    # Nettoyage cache Laravel
```

## API/Endpoints

### 🔐 Authentification (`/api/auth/`)
```http
POST   /api/auth/signin       # Connexion utilisateur
POST   /api/auth/signup       # Inscription utilisateur  
GET    /api/auth/signout      # Déconnexion (auth required)
GET    /api/auth/verify       # Vérification statut auth
```

### 🧠 Quiz Management (`/api/quizzes/`)
```http
GET    /api/quizzes                    # Liste tous les quiz
POST   /api/quizzes                    # Créer un quiz (auth)
GET    /api/quizzes/search             # Recherche quiz (Elasticsearch)
GET    /api/quizzes/{id}               # Détails d'un quiz
PUT    /api/quizzes/{id}               # Modifier quiz (auth)
DELETE /api/quizzes/{id}               # Supprimer quiz (auth)
POST   /api/quizzes/{id}/submit        # Soumettre réponses quiz
POST   /api/quizzes/{id}/attempt       # Créer tentative quiz
```

### ❓ Questions & Réponses
```http
GET    /api/questions                  # Liste questions
POST   /api/questions                  # Créer question (auth)
GET    /api/questions/{id}             # Détails question
PUT    /api/questions/{id}             # Modifier question (auth)
DELETE /api/questions/{id}             # Supprimer question (auth)
GET    /api/quizzes/{id}/questions     # Questions d'un quiz

GET    /api/answers                    # Liste réponses
POST   /api/answers                    # Créer réponse (auth)
GET    /api/answers/{id}               # Détails réponse
PUT    /api/answers/{id}               # Modifier réponse (auth)
DELETE /api/answers/{id}               # Supprimer réponse (auth)
```

### 👤 Utilisateurs & Leaderboard
```http
GET    /api/user                       # Profil utilisateur actuel (auth)
POST   /api/users/{id}/assign-badges   # Assigner badges (auth)

GET    /api/leaderboard                # Classement global
GET    /api/leaderboard/category/{id}  # Classement par catégorie
GET    /api/leaderboard/organization/{id} # Classement organisation
POST   /api/leaderboard/update-rankings   # Maj classement (auth)
```

### 🏢 Organisations & Équipes
```http
GET    /api/organizations             # Liste organisations
POST   /api/organizations             # Créer organisation (auth)
GET    /api/organizations/{id}        # Détails organisation
PUT    /api/organizations/{id}        # Modifier organisation (auth)
DELETE /api/organizations/{id}        # Supprimer organisation (auth)

GET    /api/teams                     # Liste équipes
POST   /api/teams                     # Créer équipe (auth)
GET    /api/teams/{id}                # Détails équipe
PUT    /api/teams/{id}                # Modifier équipe (auth)
DELETE /api/teams/{id}                # Supprimer équipe (auth)
```

### 🏆 Badges & Scoring
```http
GET    /api/badges                    # Liste badges
POST   /api/badges                    # Créer badge (auth)
GET    /api/badges/{id}               # Détails badge
PUT    /api/badges/{id}               # Modifier badge (auth)
DELETE /api/badges/{id}               # Supprimer badge (auth)

GET    /api/subscriptions/plans           # Plans d'abonnement disponibles
POST   /api/subscription/checkout        # Créer session checkout Stripe (auth)
POST   /api/subscription/cancel          # Annuler abonnement actuel (auth)
GET    /api/subscription/current         # Abonnement actuel utilisateur (auth)
POST   /api/webhook/stripe               # Webhook Stripe (non authentifié)
POST   /api/subscription/sync            # Synchronisation manuelle (auth)
```

### 📂 Métadonnées
```http
GET    /api/categories                # Liste catégories
GET    /api/quiz-levels              # Liste niveaux difficulté
GET    /api/question-types           # Liste types questions
GET    /api/question-responses       # Liste réponses utilisateurs
```

### Formats de réponse API
```json
{
  "items": [...],
  "meta": {
    "total": 150,
    "per_page": 10,
    "current_page": 1,
    "last_page": 15
  }
}
```

## Base de données

### Schéma principal
La base de données contient **23+ tables** avec relations complexes :

#### Tables principales
- **`users`** : Utilisateurs avec soft delete, ranking, relations org/team, subscription_plan_id
- **`subscription_plans`** : Plans d'abonnement Stripe avec limitations et prix
- **`quizzes`** : Quiz avec slug, statut, durée, score minimum
- **`questions`** : Questions liées aux quiz et types
- **`answers`** : Réponses avec flag `is_correct`
- **`question_responses`** : Réponses utilisateurs avec scoring
- **`quiz_attempts`** : Tentatives de quiz utilisateur

#### Tables de métadonnées
- **`categories`** : Catégories de quiz
- **`quiz_levels`** : Niveaux de difficulté
- **`question_types`** : Types de questions (QCM, texte, etc.)
- **`tags`** : Tags pour organisation
- **`roles`** : Rôles utilisateurs

#### Tables organisationnelles
- **`organizations`** : Structures organisationnelles
- **`teams`** : Équipes au sein d'organisations

#### Tables de gamification
- **`badges`** : Système de badges/achievements
- **`user_badges`** : Attribution badges aux utilisateurs
- **`scores`** : Historique des scores

#### Tables relationnelles
- **`quiz_user`** : Participation utilisateur aux quiz (avec score)
- **`quiz_tag`** : Association quiz-tags
- **`quiz_schedules`** : Planification des quiz

#### Tables système
- **`personal_access_tokens`** : Tokens Sanctum
- **`cache`** : Cache Laravel
- **`export_imports`** : Gestion import/export

### Relations clés
```php
### Relations clés
```php
// User relations
User hasMany Quiz (created)
User hasMany QuestionResponse
User hasMany Score
User belongsToMany Quiz (participants)
User belongsToMany Badge
User belongsTo Organization, Team, Role, SubscriptionPlan

// SubscriptionPlan relations
SubscriptionPlan hasMany User
SubscriptionPlan hasMany Subscription (via Stripe)

// Quiz relations  
Quiz hasMany Question
Quiz belongsTo User (creator), Category, QuizLevel
Quiz belongsToMany User (participants), Tag
```

// Question relations
Question belongsTo Quiz, QuestionType
Question hasMany Answer, QuestionResponse
```

### Contraintes importantes
- **Soft Deletes** : Users, Quizzes avec `deleted_at`
- **Foreign Key Constraints** : Cascade on delete pour intégrité
- **Unique Constraints** : Slugs, emails, usernames
- **Indexes** : Sur foreign keys et champs recherchés

## Tests

### Framework de test : Pest
Le projet utilise **Pest**, un framework de test moderne pour PHP, plus expressif que PHPUnit.

### Structure des tests
```
tests/
├── TestCase.php              # Classe de base pour tests
├── Pest.php                  # Configuration Pest
├── Feature/                  # Tests d'intégration
│   └── ExampleTest.php       # Test exemple API
└── Unit/                     # Tests unitaires
    ├── QuizTest.php          # Tests modèle Quiz
    ├── UserTest.php          # Tests modèle User
    ├── QuestionTest.php      # Tests modèle Question
    ├── AnswerTest.php        # Tests modèle Answer
    ├── BadgeTest.php         # Tests modèle Badge
    ├── CategoryTest.php      # Tests modèle Category
    └── QuestionTypeTest.php  # Tests modèle QuestionType
```

### Tests existants
- **Tests unitaires** : Validation modèles Eloquent, relations, validation
- **Tests fonctionnels** : Tests API endpoints avec authentification
- **Factories** : Données de test avec Faker pour tous les modèles
- **Seeders** : Population base de données test

### Commandes de test
```bash
# Exécuter tous les tests
php artisan test

# Tests avec coverage
php artisan test --coverage

# Tests spécifiques
php artisan test tests/Unit/QuizTest.php
```

### Configuration test (CI/CD)
- **Database** : Base séparée `quizify_test`
- **Environment** : `.env.testing` dédié
- **Cache** : Cache array pour performance
- **Queue** : Mode sync pour tests

## Déploiement

### 🐳 Containerisation Docker

#### Dockerfile optimisé
```dockerfile
FROM php:8.4-apache
# Extensions PHP optimisées pour production
# Composer intégré
# Apache configuré pour Laravel
```

#### Docker Compose complet
```yaml
services:
  quizify-api:
    image: quizify-api:v1
    ports: ["8000:8000"]
    healthcheck: # Monitoring santé
    environment: # Variables complètes
  
  mysql:
    image: mysql:8.0
    volumes: # Persistance données
    
  elasticsearch:
    image: elasticsearch:7.17.0
    environment: # Configuration ES
    
  adminer:
    image: adminer
    profiles: ["admin"]
```

### 🚀 CI/CD GitHub Actions

#### Pipeline automatisé (`/.github/workflows/main.yml`)
1. **Test Environment Setup**
   - PHP 8.2 avec extensions
   - MySQL 8.0 service
   - Variables d'environnement test

2. **Dependency Management**
   - Cache Composer optimisé
   - Installation dépendances
   - Génération clé application

3. **Database Setup**
   - Migrations automatiques
   - Seeders pour données test

4. **Testing Suite**
   - Tests Pest complets
   - Coverage analysis
   - Validation code quality

5. **Deployment** (branches main/dev)
   - Build image Docker
   - Push registry
   - Deploy automatique

### 📋 Healthcheck & Monitoring
```bash
# Script healthcheck.sh
curl -f http://localhost:8000/api/health || exit 1
```

### ⚙️ Variables de production
```bash
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Stripe Production
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Points d'attention

### ⚠️ Problèmes potentiels identifiés

#### 1. Gestion Elasticsearch
**Problème** : Dépendance optionnelle non gracieuse
```php
// Dans QuizController::search()
try {
    $quizzes = $builder->paginate($perPage);
} catch (\Exception $e) {
    // Fallback MySQL - BIEN ✅
    $query = Quiz::query();
    // ...
}
```
**Solution** : Le fallback MySQL est implémenté ✅

#### 2. Validation incomplète
**Problème** : `QuizController::submit()` utilise `$validated["responses"]` non défini
```php
// Bug ligne 47
$result = $this->repository->submit($user, $quizId, $validated["responses"]);
```
**Correction nécessaire** : Ajouter validation request

#### 3. Architecture Repository
**Force** : Pattern Repository bien implémenté ✅
**Amélioration** : Interfaces plus spécifiques par entité

#### 4. Gestion des erreurs
**Problème** : Exceptions pas toujours typées
**Solution** : Utiliser davantage `ApiException` et `QuizException`

#### 5. Performance base de données
**Attention** : Pas d'indexes optimisés visibles
**Suggestion** : Ajouter indexes sur colonnes recherchées fréquemment

### 🔧 Améliorations suggérées

#### 1. **Validation API renforcée**
```php
// Ajouter FormRequest pour validation complexe
class QuizSubmissionRequest extends FormRequest {
    public function rules() {
        return [
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.answer_id' => 'required_without:responses.*.user_answer|exists:answers,id',
            'responses.*.user_answer' => 'required_without:responses.*.answer_id|string'
        ];
    }
}
```

#### 2. **Cache intelligent**
```php
// Cache pour requêtes fréquentes
Cache::remember("quiz.{$id}.with.questions", 3600, function() use ($id) {
    return Quiz::with('questions.answers')->find($id);
});
```

#### 3. **Rate Limiting**
```php
// Dans routes/api.php
Route::middleware(['throttle:quiz-submission'])->group(function () {
    Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submit']);
});
```

#### 4. **Logs structurés**
```php
// Utiliser contexte dans logs
Log::info('Quiz submission', [
    'quiz_id' => $quizId,
    'user_id' => $user?->id,
    'score' => $score,
    'duration' => $duration
]);
```

#### 4. **Tests API complets**
```php
// Ajouter tests Feature pour tous les endpoints
test('quiz submission calculates correct score')
    ->actingAs($user)
    ->postJson("/api/quizzes/{$quiz->id}/submit", $responses)
    ->assertSuccessful()
    ->assertJsonStructure(['score', 'correct_answers', 'total_questions']);

test('stripe checkout creates subscription')
    ->actingAs($user)
    ->postJson('/api/subscription/checkout', ['plan_id' => 2])
    ->assertSuccessful()
    ->assertJsonStructure(['checkout_url', 'session_id']);
```

#### 5. **Monitoring Stripe**
```php
// Logs spécialisés pour Stripe
Log::info('Stripe webhook received', [
    'type' => $event->type,
    'customer_id' => $event->data->object->customer,
    'subscription_id' => $event->data->object->subscription
]);
```

### Points forts du projet

1. **Architecture modulaire** : Repository pattern, Services, Strategy pattern
2. **Recherche hybride** : Elasticsearch avec fallback MySQL
3. **Tests modernes** : Framework Pest bien configuré
4. **Docker complet** : Environnement reproductible
5. **CI/CD robuste** : Tests automatisés GitHub Actions
6. **Soft delete** : Récupération des données supprimées
7. **Relations complexes** : Modèles bien structurés
8. **API RESTful** : Endpoints cohérents et standards
9. **Authentification sécurisée** : Sanctum avec tokens
10. **Documentation complète** : README détaillé + Postman
11. **Module Stripe complet** : Paiements avec webhooks automatisés
12. **Gestion d'abonnements** : Plans avec limitations automatiques

### Métriques projet
- **25+ migrations** : Base de données complète avec Stripe
- **16+ modèles** : Entités métier avec abonnements
- **15+ contrôleurs** : API complète avec Stripe
- **7+ tests unitaires** : Couverture modèles
- **Docker ready** : Déploiement facilité
- **Elasticsearch** : Recherche performante
- **Stripe intégré** : Paiements et webhooks
- **Makefile** : Développement simplifié

#### Quiz
- GET /api/quizzes - Liste des quiz
- POST /api/quizzes - Création d'un quiz (authentifié)
- GET /api/quizzes/{id} - Détails d'un quiz
- PUT /api/quizzes/{id} - Mise à jour d'un quiz (authentifié)
- DELETE /api/quizzes/{id} - Suppression d'un quiz (authentifié)
- POST /api/quizzes/{quiz}/submit - Soumettre un quiz (authentifié)
- POST /api/quizzes/{quiz}/attempt - Créer une tentative de quiz (authentifié)

#### Questions et Réponses
- GET /api/questions - Liste des questions
- POST /api/questions - Création d'une question
- GET /api/questions/{id} - Détails d'une question
- PUT /api/questions/{id} - Mise à jour d'une question (authentifié)
- DELETE /api/questions/{id} - Suppression d'une question (authentifié)

- GET /api/answers - Liste des réponses
- POST /api/answers - Création d'une réponse (authentifié)
- GET /api/answers/{id} - Détails d'une réponse
- PUT /api/answers/{id} - Mise à jour d'une réponse (authentifié)
- DELETE /api/answers/{id} - Suppression d'une réponse (authentifié)

#### Utilisateurs
- GET /api/user - Informations sur l'utilisateur actuel (authentifié)
- POST /api/users/{user}/assign-badges - Assigner des badges à un utilisateur (authentifié)
- GET /api/leaderboard - Classement des utilisateurs

#### Organisations et Équipes
- Endpoints CRUD complets pour /api/organizations et /api/teams

#### Badges
- Endpoints CRUD complets pour /api/badges

#### Scores
- Endpoints CRUD complets pour /api/scores

#### Autres
- GET /api/question-types - Types de questions
- GET /api/quiz-levels - Niveaux de difficulté
- GET /api/categories - Catégories de quiz

## Fonctionnalités principales
1. Système d'authentification complet avec Laravel Sanctum
2. Gestion de quiz (création, modification, suppression)
3. Questions à choix multiples et autres types
4. Suivi des scores et classement (leaderboard)
5. Système de badges pour les réalisations
6. Organisation des utilisateurs (organisations, équipes)
7. Catégorisation des quiz par difficulté et thématique

## Architecture de sécurité
- Authentification via tokens (Laravel Sanctum)
- Middleware d'authentification sur les routes protégées
- Validation des entrées utilisateur

## Relations entre modèles

### User
- **role**: Belongs To `Role`
- **badges**: Belongs To Many `Badge` (via table `user_badges`)
- **quizzesCreated**: Has Many `Quiz` (créateur des quiz)
- **quizSessions**: Belongs To Many `Quiz` (participation aux quiz)
- **team**: Belongs To `Team`
- **organization**: Belongs To `Organization`
- **questionResponses**: Has Many `QuestionResponse`
- **scores**: Has Many `Score`

### Quiz
- **level**: Belongs To `QuizLevel`
- **questions**: Has Many `Question`
- **user**: Belongs To `User` (créateur)
- **participants**: Belongs To Many `User`
- **tags**: Belongs To Many `Tag`
- **category**: Belongs To `Category`

### Question
- **questionType**: Belongs To `QuestionType`
- **quiz**: Belongs To `Quiz`
- **answers**: Has Many `Answer`

### Answer
- **question**: Belongs To `Question`

### QuestionResponse
- **question**: Belongs To `Question`
- **answer**: Belongs To `Answer`
- **quiz**: Belongs To `Quiz`
- **user**: Belongs To `User`

### Category
- **quizzes**: Has Many `Quiz`

### QuestionType
- **questions**: Has Many `Question`

### QuizLevel
- **quiz**: Has Many `Quiz`

### Organization
- **teams**: Has Many `Team`
- **users**: Has Many `User`

### Team
- **organization**: Belongs To `Organization`
- **users**: Has Many `User`

### Score
- **user**: Belongs To `User`
- **quiz**: Belongs To `Quiz`

### Tag
- **quizzes**: Belongs To Many `Quiz`

### QuizAttempt
- **quiz**: Belongs To `Quiz`
- **user**: Belongs To `User`

### UserBadge
- **user**: Belongs To `User`
- **badge**: Belongs To `Badge`

### QuizSchedule
- **quiz**: Belongs To `Quiz`
- **user**: Belongs To `User`

### ExportImport
- **user**: Belongs To `User`

## Structure du projet
```
quizify-api-rest/
├── app/                             # Core application code
│   ├── Exceptions/                  # Custom exception handlers
│   │   └── Handler.php              # Global exception handler
│   ├── Http/                        # HTTP layer
│   │   ├── Controllers/             # API Controllers
│   │   │   ├── AnswerController.php     # Manages answer operations
│   │   │   ├── AuthController.php       # Handles authentication
│   │   │   ├── BadgeController.php      # Badge management
│   │   │   ├── CategoryController.php   # Category management
│   │   │   ├── OrganizationController.php # Organization operations
│   │   │   ├── QuestionController.php   # Question management
│   │   │   ├── QuestionResponseController.php # User responses to questions
│   │   │   ├── QuestionTypeController.php # Question type operations
│   │   │   ├── QuizController.php       # Quiz CRUD operations
│   │   │   ├── QuizLevelController.php  # Quiz difficulty levels
│   │   │   ├── ScoreController.php      # User score management
│   │   │   ├── TeamController.php       # Team management
│   │   │   └── UserController.php       # User operations
│   │   ├── Middleware/              # Custom middleware
│   │   │   ├── Authenticate.php         # Authentication middleware
│   │   │   └── EncryptCookies.php       # Cookie encryption
│   │   ├── Resources/               # API Resources/Transformers
│   │   │   ├── QuestionResource.php     # Question data transformation
│   │   │   └── QuizResource.php         # Quiz data transformation
│   │   └── Kernel.php               # HTTP kernel configuration
│   ├── Models/                      # Eloquent models
│   │   ├── Answer.php               # Answer model
│   │   ├── Badge.php                # Badge model
│   │   ├── Category.php             # Quiz category model
│   │   ├── Organization.php         # Organization model
│   │   ├── Question.php             # Question model
│   │   ├── QuestionResponse.php     # User's response to a question
│   │   ├── QuestionType.php         # Question type model
│   │   ├── Quiz.php                 # Quiz model
│   │   ├── QuizAttempt.php          # Quiz attempt by user
│   │   ├── QuizLevel.php            # Quiz difficulty level
│   │   ├── QuizSchedule.php         # Quiz scheduling
│   │   ├── Role.php                 # User role
│   │   ├── Score.php                # User score
│   │   ├── Tag.php                  # Content tagging
│   │   ├── Team.php                 # Team model
│   │   ├── User.php                 # User model
│   │   └── UserBadge.php            # User-Badge relationship
│   └── Providers/                   # Service providers
│       └── AppServiceProvider.php   # Main app service provider
├── bootstrap/                       # Laravel bootstrap files
├── config/                          # Configuration files
│   ├── app.php                      # Application configuration
│   ├── auth.php                     # Authentication configuration
│   ├── database.php                 # Database configuration
│   ├── filesystems.php              # File storage configuration
│   ├── sanctum.php                  # API authentication configuration
│   └── services.php                 # Third-party services
├── database/                        # Database related files
│   ├── factories/                   # Model factories for testing
│   │   ├── AnswerFactory.php        # Generate test answers
│   │   ├── BadgeFactory.php         # Generate test badges
│   │   ├── QuestionFactory.php      # Generate test questions
│   │   ├── QuizFactory.php          # Generate test quizzes
│   │   └── UserFactory.php          # Generate test users
│   ├── migrations/                  # Database migrations
│   │   ├── 2025_04_11_061650_categories.php      # Categories table
│   │   ├── 2025_04_12_085431_tags.php            # Tags table
│   │   ├── 2025_04_13_091100_quiz_levels.php     # Quiz levels table
│   │   ├── 2025_04_13_150602_roles.php           # Roles table
│   │   ├── 2025_04_13_150652_organizations.php   # Organizations table
│   │   ├── 2025_04_13_150732_teams.php           # Teams table
│   │   ├── 2025_04_13_150844_question_types.php  # Question types table
│   │   ├── 2025_04_13_150845_users.php           # Users table
│   │   ├── 2025_04_13_151400_quizzes.php         # Quizzes table
│   │   ├── 2025_04_13_151608_questions.php       # Questions table
│   │   ├── 2025_04_14_083735_answers.php         # Answers table
│   │   ├── 2025_04_14_083827_badges.php          # Badges table
│   │   └── 2025_07_06_202835_create_quiz_attempts_table.php # Quiz attempts
│   └── seeders/                     # Database seeders
│       ├── CategorySeeder.php       # Seed categories
│       ├── QuestionTypeSeeder.php   # Seed question types
│       ├── QuizLevelSeeder.php      # Seed quiz levels
│       ├── RoleSeeder.php           # Seed user roles
│       └── UserSeeder.php           # Seed users
├── docs/                            # Project documentation
│   ├── db/                          # Database documentation
│   │   └── MySqlArchitecture.md     # MySQL architecture docs
│   └── postman/                     # API testing
│       └── quizify-api.postman_collection.json # Postman collection
├── routes/                          # Route definitions
│   ├── api.php                      # API routes
│   ├── auth.php                     # Authentication routes
│   └── web.php                      # Web routes
├── tests/                           # Automated tests
│   ├── Unit/                        # Unit tests
│   │   ├── AnswerTest.php           # Answer model tests
│   │   ├── QuizTest.php             # Quiz model tests
│   │   └── UserTest.php             # User model tests
│   └── TestCase.php                 # Base test class
├── artisan                          # Laravel Artisan command-line tool
├── composer.json                    # PHP dependencies configuration
├── docker-compose.yml               # Docker Compose configuration
├── Dockerfile                       # Docker container configuration
├── LLMS.md                          # LLM context documentation (this file)
└── README.md                        # Project documentation
```
