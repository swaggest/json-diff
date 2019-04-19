phar:
	composer require php-yaoi/php-yaoi:^1;composer install --no-dev;rm -rf tests/;rm ./json-diff;rm ./json-diff.tar.gz;phar-composer build;mv ./json-diff.phar ./json-diff;tar -zcvf ./json-diff.tar.gz ./json-diff;git reset --hard;composer install

docker56-composer-update:
	test -f ./composer.phar || wget https://getcomposer.org/composer.phar
	docker run --rm -v $$(pwd):/code php:5.6-cli bash -c "apt-get update;apt-get install -y unzip;cd /code;php composer.phar update --prefer-source"

docker56-test:
	docker run --rm -v $$(pwd):/code -w /code php:5.6-cli php vendor/bin/phpunit
