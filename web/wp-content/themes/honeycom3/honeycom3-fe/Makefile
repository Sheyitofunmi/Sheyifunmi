.PHONY: templates

npm:
	npm install

gulp:
	gulp build

templates:
	test -f composer.phar || curl -sS https://getcomposer.org/installer | php
	php composer.phar install

install: npm templates
init: npm templates gulp
