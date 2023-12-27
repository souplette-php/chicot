.PHONY: test

test:
	php8.3 ./tools/phpunit.phar

coverage:
	php8.3 ./tools/phpunit.phar --coverage-html=tmp/coverage
