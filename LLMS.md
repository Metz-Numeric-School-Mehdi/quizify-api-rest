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