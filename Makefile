build:
	docker-compose build --build-arg DOCKER_UID=$(shell id -u)
	docker-compose run cli composer install

start:
	docker-compose up

stop:
	docker-compose down

shell:
	docker-compose run cli bash
