start:
	docker-compose up -d
	symfony serve -d
	@make reset-dev-db
	symfony open:local

stop:
	symfony server:stop
	docker-compose down --remove-orphan

fixtures:
	symfony console hautelook:fixtures:load --no-bundles --no-interaction --env=dev

reset-dev-db:
	symfony console doctrine:database:drop --force --if-exists --env=dev
	symfony console doctrine:database:create --if-not-exists --env=dev
	symfony console doctrine:schema:update --force --env=dev
	symfony console hautelook:fixtures:load --no-bundles --no-interaction --env=dev

reset-test-db: ## drop, create db and schema and load fixture for test env
	symfony console doctrine:database:drop --force --if-exists --env=test
	symfony console doctrine:database:create --if-not-exists --env=test
	symfony console doctrine:schema:update --force --env=test
	symfony console hautelook:fixtures:load --no-bundles --no-interaction --env=test

test: ## start test suite
	@make reset-test-db
	php bin/phpunit --stop-on-failure

lint: ## phpstan linter
	php -d memory_limit=4G vendor/bin/phpstan analyse src

cs: ## code style inspecter and fixer
	php vendor/bin/php-cs-fixer fix

quality:
	@make cs
	@make lint
	@make test

mysql-bash:
	docker exec -it recipe-api_database_1 bash -l