phar:
	composer require php-yaoi/php-yaoi:^1;composer install --no-dev;rm -rf tests/;rm ./json-diff;rm ./json-diff.tar.gz;phar-composer build;mv ./json-diff.phar ./json-diff;tar -zcvf ./json-diff.tar.gz ./json-diff;git reset --hard;composer install
