start:
	docker-compose up -d
	symfony serve -d
	symfony open:local

stop:
	symfony server:stop
	docker-compose stop