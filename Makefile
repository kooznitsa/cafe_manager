include .env

DOCKER_COMPOSE := docker compose
DOCKER_EXEC := docker exec ${APP_NAME}_php
PHP_CONSOLE := php bin/console


# -------------- DOCKER --------------

# Runs database container
.PHONY: run
run:
	$(DOCKER_COMPOSE) up -d --build

# Runs all containers
.PHONY: fullrun
fullrun:
	$(DOCKER_COMPOSE) --profile deploy up -d --build

# Removes database container
.PHONY: stop
stop:
	$(DOCKER_COMPOSE) down

# Removes all containers
.PHONY: fullstop
fullstop:
	$(DOCKER_COMPOSE) --profile deploy down

# Enters PHP container
.PHONY: entercontainer
entercontainer:
	docker exec -it ${APP_NAME}_php sh


# -------------- SYMFONY --------------

# Starts Symfony server
.PHONY: startserver
startserver:
	symfony server:start

# Creates a model and a repository
.PHONY: entity
entity:
	docker exec -it ${APP_NAME}_php $(PHP_CONSOLE) make:entity

# Lists routes
.PHONY: routes
routes:
	docker exec -it ${APP_NAME}_php $(PHP_CONSOLE) debug:router

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

# Applies migration
.PHONY: migrate
migrate:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:migrations:migrate

# Creates factory
.PHONY: factory
factory:
	$(DOCKER_EXEC) $(PHP_CONSOLE) php bin/console make:factory


# -------------- LINTER --------------

# Runs PHP_CodeSniffer through /src directory using PSR-12
# Ignores Enum as it is not supported
.PHONY: linter
linter:
	# sudo apt install php-codesniffer
	phpcs --standard=PSR12 src --ignore=/src/Enum/,/src/DTO/
