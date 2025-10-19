export $(shell sed -n 's/^\([^#]*\)=.*/\1/p' .env)

init:
	docker-compose up -d --build
	docker-compose exec php composer install
	@if ! docker compose exec php test -f .env; then \
        docker compose exec php cp .env.example .env; \
        docker compose exec php sed -i 's/DB_HOST=.*/DB_HOST=mysql/' .env; \
        docker compose exec php sed -i 's/DB_DATABASE=.*/DB_DATABASE=laravel_db/' .env; \
        docker compose exec php sed -i 's/DB_USERNAME=.*/DB_USERNAME=laravel_user/' .env; \
        docker compose exec php sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=laravel_pass/' .env; \
    fi
	@if ! docker compose exec php test -f .env.testing; then \
		docker compose exec php cp .env.testing.example .env.testing;\
		docker compose exec php sed -i 's/DB_DATABASE=.*/ DB_DATABASE=demo_test/' .env.testing; \
		docker compose exec php sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/' .env.testing; \
		docker compose exec php sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=root/' .env.testing; \
	fi
	docker compose exec php php artisan key:generate
	docker compose exec php chmod -R 777 storage bootstrap/cache
	@make fresh

fresh:
	docker compose exec php php artisan migrate:fresh --seed

restart:
	@make down
	@make up

up:
	docker-compose up -d

down:
	docker compose down --remove-orphans

cache:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:cache
stop:
	docker-compose stop

db-test-create:
	docker compose exec -T mysql mysql -u root -p$$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS demo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
