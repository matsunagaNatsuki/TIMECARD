OS := $(shell uname)
ifeq ($(OS),Darwin)          # macOS (BSD sed)
  SED_INPLACE := -i ''
else                         # Linux (GNU sed)
  SED_INPLACE := -i
endif

.PHONY: init fresh wait-db

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

	$(MAKE) wait-db
	docker compose exec php php artisan key:generate
	docker compose exec php php artisan storage:link
	docker compose exec php chmod -R 777 storage bootstrap/cache
	$(MAKE) fresh

	docker-compose exec php php artisan key:generate
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