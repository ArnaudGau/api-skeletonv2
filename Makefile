COMPOSE = docker compose
APP = $(COMPOSE) exec app

.PHONY: build up down restart shell composer-install composer-setup artisan create-api test

build:
	$(COMPOSE) up -d --build
	$(APP) composer setup

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart: down up

shell:
	$(APP) bash

composer-install:
	$(APP) composer install

composer-setup:
	$(APP) composer setup

artisan:
	$(APP) php artisan $(cmd)

create-api:
	$(APP) php artisan create-api $(name)

test:
	$(APP) composer test
