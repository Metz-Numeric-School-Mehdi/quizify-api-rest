.PHONY: build up fresh-seed adminer adminer-down clear-cache clear-config clear-route clear-view clear-all help

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

help:
	@echo ""
	@echo "\033[1;33mCommandes disponibles :\033[0m"
	@echo "  build         : Build de l'image Docker quizify-api:v1"
	@echo "  up            : Build, démarre les conteneurs et effectue migrate:fresh --seed"
	@echo "  fresh-seed    : Migration FRESH et seed de la base de données dans le conteneur quizify-api"
	@echo "  adminer       : Démarre le service Adminer avec le profil admin (port 8080)"
	@echo "  adminer-down  : Arrête le service Adminer"
	@echo "  down          : Arrête les services Docker Compose"
	@echo "  down-v        : Arrête les services Docker Compose et supprime les volumes"
	@echo "  clear-all     : Nettoie tous les caches Laravel (cache, config, route, view)"
	@echo "  help          : Affiche cette aide"
	@echo ""
