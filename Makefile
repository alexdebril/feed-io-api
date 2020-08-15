build:
	docker-compose build
	docker-compose run cli composer install

start:
	docker-compose up

stop:
	docker-compose down

shell:
	docker-compose run cli bash

test:
	docker-compose run cli composer src:test

update:
	docker-compose run cli composer update

lint:
	docker-compose run cli composer src:lint

cs-fix:
	docker-compose run cli composer src:cs-fix

stan:
	docker-compose run cli composer src:stan
