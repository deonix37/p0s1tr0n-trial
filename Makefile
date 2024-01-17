build:
	cd .docker \
	&& docker compose up -d \
	&& docker compose exec php composer install \
	&& echo "Waiting for mysql..." \
	&& sleep 30 \
	&& docker compose exec php bin/console doctrine:migrations:migrate -n \
	&& docker compose exec php bin/console app:create-admin
up:
	cd .docker \
	&& docker compose up -d
down:
	cd .docker \
	&& docker compose down
parse-books:
	cd .docker \
	&& docker compose exec php bin/console app:parse-books
