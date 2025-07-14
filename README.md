# 🧠 Quizify API REST

Welcome to Quizify - an interactive online quiz platform built with Laravel 12. ✨

![Quizify Banner](https://via.placeholder.com/800x200?text=Quizify+API+REST)

## Overview

Quizify is a full-featured quiz application that allows users to create, share and participate in quizzes. 🚀 This repository contains the backend REST API built with Laravel 12, providing all the necessary endpoints for user authentication, quiz management, question handling, scoring, and more.

## ✅ Features

- **🔐 Comprehensive Authentication System** - Secure user registration, login, and authorization
- **📝 Quiz Management** - Create, update, delete, and participate in quizzes
- **❓ Flexible Question Types** - Multiple choice, text-based, and more question formats
- **📊 Scoring System** - Track user progress and maintain a leaderboard
- **🏅 Player Ranking** - Competitive leaderboard system to rank players based on performance
- **🏆 Badge System** - Reward achievements with customizable badges
- **👥 Organizational Structure** - Manage users through organizations and teams
- **🗂️ Quiz Categorization** - Organize quizzes by difficulty level and topic
- **🏷️ Content Tagging** - Enhance discoverability with custom tags

## 💻 Tech Stack

- **PHP 8.2+**
- **Laravel 12.x**
- **Laravel Sanctum** - API authentication
- **MySQL/MariaDB** - Database

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Git

### Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/quizify-api-rest.git
   cd quizify-api-rest
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

4. Configure your database connection in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=quizify
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Run migrations and seed the database:
   ```bash
   php artisan migrate --seed
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

## 📚 API Documentation

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/signin` | User login |
| POST | `/api/auth/signup` | User registration |
| GET | `/api/auth/signout` | User logout |
| GET | `/api/auth/verify` | Verify authentication |

### Quiz Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/quizzes` | List all quizzes |
| POST | `/api/quizzes` | Create a quiz |
| GET | `/api/quizzes/{id}` | Get quiz details |
| PUT | `/api/quizzes/{id}` | Update a quiz |
| DELETE | `/api/quizzes/{id}` | Delete a quiz |
| POST | `/api/quizzes/{quiz}/submit` | Submit a completed quiz |
| POST | `/api/quizzes/{quiz}/attempt` | Create a quiz attempt |

### Question & Answer Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/questions` | List all questions |
| POST | `/api/questions` | Create a question |
| GET | `/api/questions/{id}` | Get question details |
| PUT | `/api/questions/{id}` | Update a question |
| DELETE | `/api/questions/{id}` | Delete a question |
| GET | `/api/answers` | List all answers |
| POST | `/api/answers` | Create an answer |

### User Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user` | Get current user info |
| POST | `/api/users/{user}/assign-badges` | Assign badges to a user |
| GET | `/api/leaderboard` | Get user rankings |

For a full list of endpoints, please check the Postman collection in the `/docs/postman` directory.

## 🏗️ Project Structure

```
quizify-api-rest/
├── app/                             # Core application code
│   ├── Http/                        # HTTP layer
│   │   ├── Controllers/             # API Controllers
│   │   ├── Middleware/              # Custom middleware
│   │   └── Resources/               # API Resources/Transformers
│   └── Models/                      # Eloquent models
├── database/                        # Database related files
│   ├── factories/                   # Model factories for testing
│   ├── migrations/                  # Database migrations
│   └── seeders/                     # Database seeders
├── routes/                          # Route definitions
│   ├── api.php                      # API routes
│   └── web.php                      # Web routes
└── tests/                           # Automated tests
    ├── Unit/                        # Unit tests
    └── Feature/                     # Feature tests
```

## 🧪 Testing

Run the test suite with:

```bash
php artisan test
```

## 🛠️ Makefile Commands

This project includes a Makefile to simplify common development tasks:

| Command | Description |
|---------|-------------|
| `make build` | Build the Docker image quizify-api:v1 |
| `make build-nc` | Build the Docker image without cache |
| `make up` | Build, start containers and run migrations |
| `make up-fresh` | Build, start containers with fresh migration and seed |
| `make fresh-seed` | Run fresh migrations and seed the database |
| `make adminer` | Start Adminer service on port 8080 |
| `make adminer-down` | Stop Adminer service |
| `make down` | Stop Docker Compose services |
| `make down-v` | Stop services and remove volumes |
| `make clear-all` | Clear all Laravel caches |
| `make help` | Display help information |

Example usage:

```bash
# Start the application with a fresh database
make up-fresh

# Access database management with Adminer
make adminer
```

## 🔄 CI/CD

This project uses GitHub Actions for continuous integration and deployment. The workflow runs tests, performs code quality checks, and ensures that all changes meet the required standards before being merged.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details. 📜

## 🙏 Acknowledgements

- [Laravel](https://laravel.com)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)

---

Created with ♥ by [@MehdiDiasGomes](https://github.com/MehdiDiasGomes)
