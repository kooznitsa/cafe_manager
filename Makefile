include .env

DOCKER_COMPOSE := docker compose --profile
DOCKER_EXEC := docker exec ${APP_NAME}_php
DOCKER_EXEC_IT := docker exec -it ${APP_NAME}_php
PHP_CONSOLE := php bin/console


# -------------- COMPOSER --------------

# Installs dependencies
.PHONY: install
install:
	$(DOCKER_EXEC) composer install

# Validates composer
.PHONY: validate
validate:
	$(DOCKER_EXEC) composer validate


# -------------- DOCKER --------------

# Runs php and database containers
.PHONY: run
run:
	$(DOCKER_COMPOSE) cm up -d --build

# Removes php and database containers
.PHONY: stop
stop:
	$(DOCKER_COMPOSE) cm down

# Runs basic containers + RabbitMQ
.PHONY: grafrun
grafrun:
	$(DOCKER_COMPOSE) rabbit up -d --build

# Removes basic containers + RabbitMQ
.PHONY: grafstop
grafstop:
	$(DOCKER_COMPOSE) rabbit down

# Runs basic containers + Sentry containers
.PHONY: sentryrun
sentryrun:
	$(DOCKER_COMPOSE) sentry up -d --build

# Removes basic containers + Sentry containers
.PHONY: sentrystop
sentrystop:
	$(DOCKER_COMPOSE) sentry down

# Enters PHP container
.PHONY: entercontainer
entercontainer:
	$(DOCKER_EXEC_IT) sh


# -------------- SYMFONY --------------

# Starts Symfony server
.PHONY: startserver
startserver:
	symfony server:start

# Creates a model and a repository
.PHONY: entity
entity:
	$(DOCKER_EXEC_IT) $(PHP_CONSOLE) make:entity

# Lists routes
.PHONY: routes
routes:
	$(DOCKER_EXEC_IT) $(PHP_CONSOLE) debug:router

# Clears cache
.PHONY: clearcache
clearcache:
	$(DOCKER_EXEC) $(PHP_CONSOLE) cache:clear


# -------------- DOCTRINE --------------

# Validates schema
.PHONY: validateschema
validateschema:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:schema:validate

# Shows differences between schema and database
.PHONY: dbdiff
dbdiff:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:schema:update --dump-sql

# Creates migration
.PHONY: makemigration
makemigration:
	$(DOCKER_EXEC) $(PHP_CONSOLE) make:migration --formatted

# Applies migrations
.PHONY: migrate
migrate:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:migrations:migrate

# Applies migrations to test database
.PHONY: testmigrate
testmigrate:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:migrations:migrate --env=test


# -------------- TESTS --------------

# Launches unit tests
.PHONY: unittest
unittest:
	$(DOCKER_EXEC) ./vendor/bin/simple-phpunit

# Creates factory
.PHONY: factory
factory:
	$(DOCKER_EXEC_IT) $(PHP_CONSOLE) make:factory --all-fields

# Loads fixtures
.PHONY: loadfixtures
loadfixtures:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:fixtures:load --env=test --purge-with-truncate --no-interaction


# -------------- RABBITMQ --------------

# Launches consumer
.PHONY: q
q:
	$(DOCKER_EXEC) $(PHP_CONSOLE) rabbitmq:consumer create_order


# -------------- LINTER --------------

# Runs PHP_CodeSniffer through /src directory using PSR-12
# Ignores Enum as it is not supported
.PHONY: linter
linter:
	# sudo apt install php-codesniffer
	phpcs --standard=PSR12 src --ignore=/src/Enum/,/src/DTO/
