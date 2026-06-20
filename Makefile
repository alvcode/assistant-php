-include .env.local
-include ../secrets/.env

start:
	docker compose up -d;

stop:
	docker compose down;

create-needed:
	sudo mkdir uploads
	sudo chown -R www-data:www-data uploads
	sudo chmod -R 755 uploads

# ========================================================= PROD ==========================================
build-prod:
	docker compose -f docker-compose-prod.yaml up -d --build;

start-prod:
	docker compose -f docker-compose-prod.yaml up -d;

stop-prod:
	docker compose -f docker-compose-prod.yaml down;

deploy:
	git pull;
	make composer-install;
	make stop-prod;
	make start-prod;
	make m;

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

stan:
	docker exec -it ast-app vendor/bin/phpstan analyse --memory-limit 2G -c phpstan/level-6.neon

console-test:
	docker exec -it ast-app php bin/console test

# ========================================================= BACKUP/RESTORE DATABASE ==========================================

backup-db:
	docker exec ast-db pg_dump -U $(DB_USER) -d $(DB_DATABASE) | gzip > $(DB_LOCAL_BACKUP_PATH)/$(shell date +%Y-%m-%d_%H-%M-%S).sql.gz
	chown -R $(DB_LOCAL_BACKUP_OWNER):$(DB_LOCAL_BACKUP_OWNER) $(DB_LOCAL_BACKUP_PATH);
	echo "Database backup created successfully"

db-remove-old-backups: # Удаляет бэкапы БД, которые были созданы более 5 дней назад
	find $(DB_LOCAL_BACKUP_PATH) -type f -mtime +5 -exec rm -rf {} +

restore-db: # with param file=path/to/backup/dump.sql.gz
	gunzip -c $(file) | docker exec -i ast-db psql -U $(DB_USER) -d $(DB_DATABASE)
	echo "Database restored successfully"

# ========================================================= PRODUCTION COMMANDS ==========================================
clear-cache-prod:
	docker exec -it ast-app bin/console cache:clear --env=prod

clear-cache-dev:
	docker exec -it ast-app bin/console cache:clear --env=dev

jwt-gen:
	docker exec -it ast-app bin/console lexik:jwt:generate-keypair

jwt-gen-overwrite:
	docker exec -it ast-app bin/console lexik:jwt:generate-keypair --overwrite
