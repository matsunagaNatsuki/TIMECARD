init:
	docker-compose up -d --build
	docker-compose exec php composer install
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		sed -i '' 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env;
		sed -i '' 's/DB_DATABASE=laravel/DB_DATABASE=laravel_db/' .env;
		sed -i '' 's/DB_USERNAME=root/DB_USERNAME=laravel_user/' .env;
		sed -i '' 's/DB_PASSWORD=.*/DB_PASSWORD=laravel_pass/' .env;

	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan storage:link
	docker-compose exec php chmod -R 777 storage bootstrap/cache
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