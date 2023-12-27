.PHONY: phar, test, coverage

phar:
	@./tools/box compile
	@mv bin/chicot.phar build
	@gpg -u jules.bernable@gmail.com --detach-sign \
		--output build/chicot.phar.asc \
		build/chicot.phar

test:
	php8.3 ./tools/phpunit.phar

coverage:
	php8.3 ./tools/phpunit.phar --coverage-html=tmp/coverage
