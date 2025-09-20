# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2025-09-20

### Added
- Introduced a complete REST API for quiz management, including endpoints for quizzes, questions, answers, users, organizations, teams, badges, scores, and more.
- Implemented user authentication and registration with Laravel Sanctum, supporting token-based access and role management.
- Developed a subscription module with Stripe integration, supporting Free, Premium, and Business plans, automated webhooks, and plan limitations.
- Enabled advanced quiz features: multiple question types (MCQ, free text), tagging, difficulty levels, draft/publish status, and soft deletes.
- Integrated Elasticsearch for high-performance full-text search with automatic fallback to MySQL.
- Added leaderboard and scoring system with global, category, and organizational rankings, plus achievement badges.
- Provided organization and team management, including hierarchical structures and differentiated user roles.
- Delivered analytics and tracking: quiz attempts, question responses, user progress, and import/export capabilities.
- Containerized the application with Docker and Docker Compose, including services for MySQL, Elasticsearch, and Adminer.
- Established a robust testing suite using Pest, with unit and feature tests, model factories, and seeders.
- Set up CI/CD pipeline with GitHub Actions for automated testing, code quality checks, and deployment.
- Supplied comprehensive documentation, including API references, database architecture, and Postman collections.

### Security
- Enhanced authentication and authorization with stricter middleware and token management.
- Improved password security using bcrypt and complex validation rules.
- Implemented rate limiting and input validation to prevent abuse and injection attacks.
- Secured Stripe integration with webhook secret validation and error logging.
- Hardened production environment with optimized Docker images, disabled debug mode, and secure environment variables.

---

**Contributors:**  
Special thanks to the main contributor: @MehdiDiasGomes for all work on features, bug fixes, and documentation.

For more details, see related issues and pull requests on GitHub.
