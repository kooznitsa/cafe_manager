include .env

ENV ?= 'dev'

DOCKER_COMPOSE := docker compose --profile
DOCKER_EXEC := docker exec ${APP_NAME}_php
DOCKER_EXEC_IT := docker exec -it ${APP_NAME}_php
DOCKER_PROFILE ?= cm
PHP_CONSOLE := php bin/console

USER_EMAIL ?= ${APP_EMAIL}
USER_PASSWORD ?= ${APP_PASSWORD}
TEST_DIR ?= 'tests'


# -------------- COMPOSER --------------

# Installs dependencies
.PHONY: install
install:
	$(DOCKER_EXEC) composer install

# Validates composer
.PHONY: validate
validate:
	$(DOCKER_EXEC) composer validate

# Dump autoload
.PHONY: dump
dump:
	$(DOCKER_EXEC) composer dump-autoload


# -------------- DOCKER --------------

# Runs php and database containers
# DOCKER_PROFILE options: cm, rabbit, sentry. Example: make run DOCKER_PROFILE=rabbit
.PHONY: run
run:
	$(DOCKER_COMPOSE) $(DOCKER_PROFILE) up -d --build

# Removes php and database containers
# DOCKER_PROFILE options: cm, rabbit, sentry. Example: make stop DOCKER_PROFILE=rabbit
.PHONY: stop
stop:
	$(DOCKER_COMPOSE) $(DOCKER_PROFILE) down

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
# ENV options: dev, test. Example: make migrate ENV=test
.PHONY: migrate
migrate:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:migrations:migrate --env=$(ENV)


# -------------- TESTS --------------

# Launches PHPUnit tests
.PHONY: unittest
unittest:
	$(DOCKER_EXEC) ./vendor/bin/simple-phpunit

# Launches Codeception tests
# TEST_DIR options: tests, tests/Acceptance, tests/Functional, tests/Unit. Example: make test TEST_DIR=tests/Acceptance
.PHONY: test
test:
	$(DOCKER_EXEC) vendor/bin/codecept run $(TEST_DIR)

# Creates factory
.PHONY: factory
factory:
	$(DOCKER_EXEC_IT) $(PHP_CONSOLE) make:factory --all-fields

# Loads fixtures
# ENV options: dev, test. Example: make loadfixtures ENV=test
.PHONY: loadfixtures
loadfixtures:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:fixtures:load --env=$(ENV) --purge-with-truncate --no-interaction

# Build Codeception configuration (after editing .yml/.yaml files)
.PHONY: build-codeception
build-codeception:
	$(DOCKER_EXEC) ./vendor/bin/codecept build

# Creates user
# Example: make create-user USER_EMAIL=test5@email.com USER_PASSWORD=TSshark1957work$
.PHONY: createuser
createuser:
	$(DOCKER_EXEC) php bin/console user:add $(USER_EMAIL) $(USER_PASSWORD)


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
	phpcs --standard=PSR12 src --ignore=/src/Enum/,/src/DTO/


# -------------- DOCS --------------

# Generates API docs in .yaml format
.PHONY: apidoc
apidoc:
	$(DOCKER_EXEC) $(PHP_CONSOLE) nelmio:apidoc:dump --format=yaml >apidoc.yaml


# -------------- SETUP --------------

# Launches cloned project
.PHONY: setup
setup:
	sudo apt install php-codesniffer
	make run install migrate loadfixtures createuser
