build:
	docker-compose build
	docker-compose run cli composer install

start:
	docker-compose up

stop:
	docker-compose down

shell:
	docker-compose run cli bash

test-api:
	docker-compose run newman run /etc/postman/feed-io-api.postman_collection.json
	docker-compose down --volumes --remove-orphans

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
