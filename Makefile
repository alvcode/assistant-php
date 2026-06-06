include .env

start:
	docker compose up -d;

stop:
	docker compose down;

# ========================================================= migrations / entity ==========================================
mc:
	docker exec -it ast-app bin/console doctrine:migrations:generate

m:
	docker exec -it ast-app bin/console doctrine:migrations:migrate

mr:
	docker exec -it ast-app bin/console doctrine:migrations:migrate prev

md:
	docker exec -it ast-app bin/console doctrine:migrations:diff

ec: # name=... Название сущности
	docker exec -it ast-app bin/console make:entity $(name)


# ========================================================= COMPOSER/APP ==========================================
composer-install:
	docker exec -it ast-app composer install -n;

composer-update:
	docker exec -it ast-app composer update -n;

composer-update-package: # Вызывается с параметром package=xxx
	docker exec -it ast-app composer update -n -W $(package);

composer-req: # Вызывается с параметром package=xxx
	docker exec -it ast-app composer require $(package)

composer-remove: # Вызывается с параметром package=xxx
	docker exec -it ast-app composer remove $(package)

back-bash:
	docker exec -it ast-app bash;

pgs-bash:
	docker exec -it ast-db bash;

test:
	docker exec ast-app vendor/bin/phpunit

# ========================================================= PRODUCTION COMMANDS ==========================================
clear-cache-prod:
	docker exec -it ast-app bin/console cache:clear --env=prod

clear-cache-dev:
	docker exec -it ast-app bin/console cache:clear --env=dev

jwt-gen:
	docker exec -it ast-app bin/console lexik:jwt:generate-keypair

jwt-gen-overwrite:
	docker exec -it ast-app bin/console lexik:jwt:generate-keypair --overwrite
