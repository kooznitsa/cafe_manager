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


# -------------- SYMFONY --------------

# Starts Symfony server
.PHONY: startserver
startserver:
	symfony server:start

# Creates a model and a repository
.PHONY: entity
entity:
	$(PHP_CONSOLE) make:entity


# -------------- DOCTRINE --------------

# Applies migration
.PHONY: migrate
migrate:
	$(DOCKER_EXEC) $(PHP_CONSOLE) doctrine:migrations:migrate


# -------------- LINTER --------------

# Runs PHP_CodeSniffer through /src directory using PSR-12
.PHONY: linter
linter:
	# sudo apt install php-codesniffer
	phpcs --standard=PSR12 src
