include .env
update:
	git stash
	git pull origin master
	#mv ./nginx/wordpress_ssl.conf ./nginx/wordpress_ssl.local
	#mv ./nginx/wordpress_ssl.dev.conf ./nginx/wordpress_ssl.conf
	# chmod 666 ./wp-app/wp-content/wp-cache-config.php
	chmod 755 ./wp-app/.htaccess
	chmod 755 ./wp-app/wp-content/fancy_products_orders/
	chmod 755 ./wp-app/wp-content/uploads/
	chmod 755 ./logs/
	chmod 755 ./logs/Transbank_webpay
	docker-compose down
	make build
	docker-compose up -d
backup:
	cp -r ./wp-data/* ./backups
	rm -r ./wp-data/*
	sh ./scripts/export.sh
start:
	docker-compose up -d mysql redis
	./scripts/waitMysql.sh
	docker-compose up --build woo
stop:
	docker-compose down
restart:
	docker-compose down
	make start
release:
	cp -r ./wp-data/* ./backups
	rm -r ./wp-data/*
	sh ./export.sh
	git add --all
	git commit -m "new release"
	git push origin master
build:
	docker-compose build woo_prod
push:
	docker push gcr.io/fluent-cd90c/woocommerce:1.19.9
publish:
	make build
	make push
runscript:
	docker-compose build nodecron
	docker-compose up nodecron
deploy:
	sh ./kubernetes/deploy.sh