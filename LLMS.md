# Quizify API REST - Documentation pour LLM

## Vue d'ensemble du projet
Quizify est une application de quiz en ligne qui permet aux utilisateurs de créer, partager et répondre à des quiz. Ce projet implémente l'API REST backend basée sur le framework Laravel 12.

## Structure technique

### Framework et langage
- PHP 8.2+
- Laravel 12.x
- Laravel Sanctum pour l'authentification API

### Dépendances principales
- Laravel Framework 12.0
- Laravel Sanctum 4.0 (authentification API)
- Laravel Tinker 2.10.1
- AWS S3 support via league/flysystem-aws-s3-v3 3.0

### Dépendances de développement
- Faker pour les données de test
- Laravel Sail (environnement Docker)
- Laravel Pint (formatage de code)
- Pest (framework de test)

## Architecture du projet

### Modèles principaux
1. **User** - Utilisateurs du système
2. **Quiz** - Quiz créés par les utilisateurs
3. **Question** - Questions appartenant à un quiz
4. **Answer** - Réponses possibles à une question
5. **QuestionType** - Types de questions (QCM, texte libre, etc.)
6. **QuizAttempt** - Tentatives de réalisation d'un quiz par un utilisateur
7. **QuestionResponse** - Réponses données par les utilisateurs
8. **Score** - Scores des utilisateurs
9. **Badge** - Badges attribuables aux utilisateurs
10. **UserBadge** - Association entre utilisateurs et badges
11. **Organization** - Organisations auxquelles peuvent appartenir les utilisateurs
12. **Team** - Équipes au sein des organisations
13. **Category** - Catégories de quiz
14. **QuizLevel** - Niveaux de difficulté des quiz
15. **Tag** - Tags pour les quiz
16. **QuizSchedule** - Planification des quiz

### Points d'entrée API principaux

#### Authentification
- POST /api/auth/signin - Connexion utilisateur
- POST /api/auth/signup - Inscription utilisateur
- GET /api/auth/signout - Déconnexion
- GET /api/auth/verify - Vérification d'authentification

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