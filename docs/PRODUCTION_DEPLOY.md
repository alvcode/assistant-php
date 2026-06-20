Создать папку "secrets" рядом с папкой проекта и скопировать туда .env.example 
изменив название на .env.prod и настроив под свои нужды
```
- assistant-php
---- bin/
---- config/
---- public/
---- src/
---- vendor/
---- ...............
- secrets
---- .env.prod
```
<br></br>
Запускаем команды
```
make build-prod
make composer-install
make m
make create-required
```

Добавляем в nginx, который будет проксировать запросы 
до nginx контейнера
```
 client_max_body_size 65m;
 server_tokens off;
        
 location / {
        proxy_pass http://127.0.0.1:8077;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
 }       
```

Set crontab
```
10 1 * * * cd /var/www/assistant-php && make backup-db

#Deletes old backups, leaving the last 5
40 5 * * * cd /var/www/assistant-php && make db-remove-old-backups

#Cleans up stale records in the database
0 4 25 * * cd /var/www/assistant-php && make cli-clean-db
```
<br></br>

<h4>Команды</h4>
```
make deploy - стянет все изменения, накатит миграции и т.д...
make stop-prod - остановка контейнеров
make start-prod - запуск контейнеров
```
