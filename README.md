# Quizify API REST

Plateforme de quiz interactive complète construite avec Laravel 12 et intégration Stripe.

![Quizify Banner](https://via.placeholder.com/800x200?text=Quizify+API+REST)

## Vue d'ensemble

Quizify est une application de quiz complète qui permet aux utilisateurs de créer, partager et participer à des quiz avec un système d'abonnement Stripe intégré. Ce repository contient l'API REST backend construite avec Laravel 12, fournissant tous les endpoints nécessaires pour l'authentification, la gestion des quiz, le système de scoring, les abonnements et plus encore.

## Fonctionnalités

- **Système d'authentification complet** - Inscription, connexion et autorisation sécurisées
- **Gestion des quiz** - Créer, modifier, supprimer et participer aux quiz
- **Types de questions flexibles** - Choix multiples, texte libre et autres formats
- **Système de scoring** - Suivi des progrès et classement des utilisateurs
- **Classement des joueurs** - Système de leaderboard compétitif
- **Système de badges** - Récompenses d'achievements personnalisables
- **Structure organisationnelle** - Gestion des utilisateurs via organisations et équipes
- **Catégorisation des quiz** - Organisation par niveau de difficulté et thématique
- **Système de tags** - Amélioration de la découvrabilité avec tags personnalisés
- **Module Stripe complet** - Trois plans d'abonnement avec paiements automatisés
- **Limitations automatiques** - Contrôle d'accès selon les plans d'abonnement
- **Webhooks Stripe** - Synchronisation automatique des abonnements

## Stack technique

- **PHP 8.2+**
- **Laravel 12.x**
- **Laravel Sanctum** - Authentification API
- **Laravel Cashier** - Intégration Stripe
- **Stripe** - Paiements et abonnements
- **MySQL/MariaDB** - Base de données
- **Elasticsearch** - Recherche avancée (optionnel)
- **Docker** - Conteneurisation
- **Pest** - Tests modernes

## Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL/MariaDB
- Docker & Docker Compose (recommandé)
- Stripe CLI (pour le développement avec webhooks)
- Git

### Installation avec Docker (Recommandée)

1. Cloner le repository :
   ```bash
   git clone https://github.com/MehdiDiasGomes/quizify-api-rest.git
   cd quizify-api-rest
   ```

2. Copier la configuration :
   ```bash
   cp .env.example .env
   ```

3. Configurer les variables Stripe dans `.env` :
   ```bash
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

4. Démarrer l'api :
   ```bash
   make up
   ```

5. L'application est accessible sur http://localhost:8000

### Installation manuelle

1. Cloner le repository :
   ```bash
   git clone https://github.com/yourusername/quizify-api-rest.git
   cd quizify-api-rest
   ```

2. Installer les dépendances :
   ```bash
   composer install
   ```

3. Copier le fichier d'environnement :
   ```bash
   cp .env.example .env
   ```

4. Configurer la base de données dans `.env` :
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=quizify
   DB_USERNAME=root
   DB_PASSWORD=password
   ```

5. Configurer Stripe dans `.env` :
   ```
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

6. Générer la clé d'application :
   ```bash
   php artisan key:generate
   ```

7. Exécuter les migrations et seeders :
   ```bash
   php artisan migrate:fresh --seed
   ```

8. Démarrer le serveur :
   ```bash
   php artisan serve
   ```

### Configuration des webhooks Stripe (Développement)

1. Installer Stripe CLI :
   ```bash
   # macOS
   brew install stripe/stripe-cli/stripe
   
   # Windows/Linux
   # Voir : https://stripe.com/docs/stripe-cli
   ```

2. Se connecter à Stripe :
   ```bash
   stripe login
   ```

3. Démarrer l'écoute des webhooks :
   ```bash
   stripe listen --forward-to localhost:8000/api/webhook/stripe --events checkout.session.completed,customer.subscription.updated,invoice.payment_succeeded
   ```

4. Copier le webhook secret affiché et l'ajouter dans `.env` :
   ```bash
   STRIPE_WEBHOOK_SECRET=whsec_...
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
## Documentation API

### Endpoints d'authentification

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/signin` | Connexion utilisateur |
| POST | `/api/auth/signup` | Inscription utilisateur |
| GET | `/api/auth/signout` | Déconnexion utilisateur |
| GET | `/api/auth/verify` | Vérification authentification |

### Endpoints des quiz

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/quizzes` | Liste de tous les quiz |
| POST | `/api/quizzes` | Créer un quiz |
| GET | `/api/quizzes/{id}` | Détails d'un quiz |
| PUT | `/api/quizzes/{id}` | Modifier un quiz |
| DELETE | `/api/quizzes/{id}` | Supprimer un quiz |
| POST | `/api/quizzes/{id}/submit` | Soumettre les réponses |
| POST | `/api/quizzes/{id}/attempt` | Créer une tentative |

### Endpoints d'abonnement Stripe

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/subscriptions/plans` | Liste des plans disponibles |
| POST | `/api/subscription/checkout` | Créer session checkout Stripe |
| POST | `/api/subscription/cancel` | Annuler l'abonnement actuel |
| GET | `/api/subscription/current` | Abonnement actuel de l'utilisateur |
| POST | `/api/webhook/stripe` | Webhook Stripe (non authentifié) |
| POST | `/api/subscription/sync` | Synchronisation manuelle |
| POST | `/api/quizzes/{quiz}/submit` | Submit a completed quiz |
| POST | `/api/quizzes/{quiz}/attempt` | Create a quiz attempt |

### Question & Answer Endpoints

### Endpoints des questions et réponses

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/questions` | Liste de toutes les questions |
| POST | `/api/questions` | Créer une question |
| GET | `/api/questions/{id}` | Détails d'une question |
| PUT | `/api/questions/{id}` | Modifier une question |
| DELETE | `/api/questions/{id}` | Supprimer une question |
| GET | `/api/answers` | Liste de toutes les réponses |
| POST | `/api/answers` | Créer une réponse |

### Endpoints utilisateur

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/user` | Informations utilisateur actuel |
| POST | `/api/users/{user}/assign-badges` | Assigner des badges |
| GET | `/api/leaderboard` | Classement des utilisateurs |

Pour la liste complète des endpoints, consultez la collection Postman dans `/docs/postman`.

## Plans d'abonnement Stripe

### Plan Gratuit (0€/mois)
- 3 quiz maximum
- 10 questions par quiz
- 50 participants par quiz
- Statistiques de base

### Plan Premium (9.99€/mois)
- Quiz illimités
- 50 questions par quiz
- 500 participants par quiz
- Analytics avancées
- Export complet

### Plan Business (29.99€/mois)
- Tout illimité
- Gestion d'équipes
- Support prioritaire
- API personnalisée

## Structure du projet

```
quizify-api-rest/
├── app/                             # Code applicatif principal
│   ├── Http/                        # Couche HTTP
│   │   ├── Controllers/             # Contrôleurs API
│   │   │   ├── SubscriptionController.php # Gestion Stripe
│   │   │   └── ...
│   │   ├── Middleware/              # Middlewares personnalisés
│   │   └── Resources/               # Transformateurs API
│   ├── Models/                      # Modèles Eloquent
│   │   ├── SubscriptionPlan.php     # Plans d'abonnement
│   │   └── ...
│   └── Services/                    # Services métier
│       ├── SubscriptionService.php  # Logique Stripe
│       └── ...
├── database/                        # Base de données
│   ├── migrations/                  # Migrations avec Stripe
│   └── seeders/                     # Seeders avec plans
├── routes/                          # Définition des routes
│   ├── api.php                      # Routes API avec Stripe
│   └── ...
└── tests/                           # Tests automatisés
    ├── Unit/                        # Tests unitaires
    └── Feature/                     # Tests d'intégration
```

## Tests

Exécuter la suite de tests :

```bash
php artisan test
```

Tests avec coverage :

```bash
php artisan test --coverage
```

## Développement et Debugging

### Variables d'environnement importantes

```bash
# Application
APP_NAME=Quizify
APP_ENV=local
APP_DEBUG=true

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=quizify

# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Elasticsearch (optionnel)
SCOUT_DRIVER=elasticsearch
ELASTICSEARCH_HOST=localhost:9200
```

### Commandes utiles

```bash
# Nettoyage cache complet
php artisan config:clear && php artisan cache:clear && php artisan route:clear

# Migration fresh avec seeders
php artisan migrate:fresh --seed

# Création d'un contrôleur
php artisan make:controller NomController --api

# Création d'un modèle avec migration
php artisan make:model NomModel -m

# Tests spécifiques
php artisan test tests/Unit/SubscriptionTest.php
```

### Debugging Stripe

1. Vérifier les logs webhook :
   ```bash
   tail -f storage/logs/laravel.log | grep -i "webhook\|stripe"
   ```

2. Tester un webhook :
   ```bash
   stripe trigger checkout.session.completed
   ```

3. Voir les événements Stripe :
   ```bash
   stripe events list --limit 10
   ```

## Production et Déploiement

### Variables de production

```bash
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning

# Stripe Production
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Cache et sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Commandes de déploiement

```bash
# Optimisation production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migration en production
php artisan migrate --force

# Installation sans dev dependencies
composer install --no-dev --optimize-autoloader
```

## Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Support

| `make fresh-seed` | Run fresh migrations and seed the database |
| `make adminer` | Start Adminer service on port 8080 |
| `make adminer-down` | Stop Adminer service |
| `make down` | Stop Docker Compose services |
| `make down-v` | Stop services and remove volumes |
| `make clear-all` | Clear all Laravel caches |
| `make help` | Display help information |
Exemple d'utilisation :

```bash
# Démarrer l'application avec une base de données fraîche
make up-fresh

# Accéder à la gestion de la base de données avec Adminer
make adminer
```

## 🔄 Intégration et Déploiement Continus (CI/CD)

Ce projet utilise GitHub Actions pour l'intégration et le déploiement continus. Le workflow exécute les tests, effectue des vérifications de qualité du code et s'assure que toutes les modifications respectent les standards requis avant d'être fusionnées.

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails. 📜

---

Créé avec ♥ par [@MehdiDiasGomes](https://github.com/MehdiDiasGomes)
