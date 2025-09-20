.PHONY: build up fresh-seed adminer adminer-down clear-cache clear-config clear-route clear-view clear-all ssl help
ssl:
	@echo "\033[1;34m[INFO]\033[0m Génération des clés SSL dans le dossier certs..."
	mkdir -p certs
	openssl req -x509 -nodes -days 365 \
	  -newkey rsa:2048 \
	  -keyout certs/quizify.local.key \
	  -out certs/quizify.local.crt \
	  -subj "/C=FR/ST=Paris/L=Paris/O=Quizify/OU=Dev/CN=quizify.local"
	@echo "\033[1;32m[SUCCESS]\033[0m Clés SSL générées dans certs/ !"

build:
	@echo "\033[1;34m[INFO]\033[0m Build de l'image Docker quizify-api:v1..."
	@docker build -t quizify-api:v1 .
	@echo "\033[1;32m[SUCCESS]\033[0m Build terminé !"

build-nc:
	@echo "\033[1;34m[INFO]\033[0m Build de l'image Docker quizify-api:v1 sans cache..."
	@docker build -t quizify-api:v1 . --no-cache
	@echo "\033[1;32m[SUCCESS]\033[0m Build sans cache terminé !"

up:
	@$(MAKE) build
	@echo "\033[1;34m[INFO]\033[0m Démarrage du conteneur quizify-api:v1..."
	@docker compose up -d
	@echo "\033[1;32m[SUCCESS]\033[0m Conteneur prêt avec base fraîchement migrée et seedée !"

up-fresh:
	@$(MAKE) build
	@echo "\033[1;34m[INFO]\033[0m Démarrage du conteneur quizify-api:v1..."
	@docker compose up -d
	@$(MAKE) fresh-seed
	@echo "\033[1;32m[SUCCESS]\033[0m Conteneur prêt avec base fraîchement migrée et seedée !"

fresh-seed:
	@echo "\033[1;34m[INFO]\033[0m Migration FRESH et seed de la base de données dans le conteneur quizify-api..."
	@docker compose exec quizify-api php artisan migrate:fresh --seed --force
	@echo "\033[1;32m[SUCCESS]\033[0m Migration FRESH et seed terminés !"

fresh-seed-search:
	@echo "\033[1;34m[INFO]\033[0m Migration FRESH, seed et réindexation Elasticsearch..."
	@docker compose exec quizify-api php artisan migrate:fresh --seed --force
	@docker compose exec quizify-api php artisan search:reindex
	@echo "\033[1;32m[SUCCESS]\033[0m Migration FRESH, seed et réindexation terminés !"

adminer:
	@echo "\033[1;34m[INFO]\033[0m Démarrage du service Adminer avec le profil admin..."
	@docker compose --profile admin up -d adminer
	@echo "\033[1;32m[SUCCESS]\033[0m Adminer est démarré sur le port 8080 !"

adminer-down:
	@echo "\033[1;34m[INFO]\033[0m Arrêt du service Adminer..."
	@docker compose stop adminer
	@echo "\033[1;32m[SUCCESS]\033[0m Adminer est arrêté."

down:
	@echo "\033[1;34m[INFO]\033[0m Arrêt des services Docker Compose..."
	@docker compose down
	@echo "\033[1;32m[SUCCESS]\033[0m Services arrêtés !"

down-v:
	@echo "\033[1;34m[INFO]\033[0m Arrêt des services Docker Compose et suppression des volumes..."
	@docker compose down -v
	@echo "\033[1;32m[SUCCESS]\033[0m Services et volumes arrêtés et supprimés !"

clear-all:
	@echo "\033[1;34m[INFO]\033[0m Nettoyage de tous les caches Laravel (cache, config, route, view)..."
	@docker compose exec quizify-api php artisan optimize:clear
	@echo "\033[1;32m[SUCCESS]\033[0m Tous les caches Laravel ont été nettoyés !"

test:
	@echo "\033[1;34m[INFO]\033[0m Exécution des tests PHPUnit dans le conteneur quizify-api..."
	php artisan test
	@echo "\033[1;32m[SUCCESS]\033[0m Tests PHPUnit terminés !"

test-coverage:
	@echo "\033[1;34m[INFO]\033[0m Exécution des tests avec code coverage..."
	@mkdir -p storage/coverage
	@docker exec quizify-api-rest-quizify-api-1 composer test-coverage
	@echo "\033[1;32m[SUCCESS]\033[0m Tests avec coverage terminés !"

test-coverage-html:
	@echo "\033[1;34m[INFO]\033[0m Génération du rapport HTML de code coverage..."
	@mkdir -p storage/coverage/html
	@docker exec quizify-api-rest-quizify-api-1 composer test-coverage-html
	@echo "\033[1;32m[SUCCESS]\033[0m Rapport HTML généré dans storage/coverage/html/ !"

test-coverage-ci:
	@echo "\033[1;34m[INFO]\033[0m Génération des rapports de coverage pour CI/CD..."
	@mkdir -p storage/coverage
	@docker exec quizify-api-rest-quizify-api-1 composer coverage-all
	@echo "\033[1;32m[SUCCESS]\033[0m Rapports de coverage générés !"

coverage-open:
	@echo "\033[1;34m[INFO]\033[0m Ouverture du rapport HTML de coverage..."
	@open storage/coverage/html/index.html || xdg-open storage/coverage/html/index.html || echo "Ouvrez manuellement : storage/coverage/html/index.html"

help:
	@echo ""
	@echo "\033[1;33mCommandes disponibles :\033[0m"
	@echo "  build             : Build de l'image Docker quizify-api:v1"
	@echo "  up                : Build, démarre les conteneurs et effectue migrate:fresh --seed"
	@echo "  fresh-seed        : Migration FRESH et seed de la base de données"
	@echo "  fresh-seed-search : Migration FRESH, seed et réindexation Elasticsearch"
	@echo "  adminer           : Démarre le service Adminer (port 8080)"
	@echo "  adminer-down      : Arrête le service Adminer"
	@echo "  down              : Arrête les services Docker Compose"
	@echo "  down-v            : Arrête les services et supprime les volumes"
	@echo "  clear-all         : Nettoie tous les caches Laravel"
	@echo "  test              : Exécute les tests PHPUnit"
	@echo "  test-coverage     : Tests avec coverage de base"
	@echo "  test-coverage-html: Tests avec rapport HTML de coverage"
	@echo "  test-coverage-ci  : Tests avec tous les rapports pour CI/CD"
	@echo "  coverage-open     : Ouvre le rapport HTML de coverage"
	@echo "  ssl               : Génère les clés SSL dans le dossier certs/"
	@echo "  help              : Affiche cette aide"
	@echo ""
