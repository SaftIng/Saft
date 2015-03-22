PHPCS = ./vendor/squizlabs/php_codesniffer/scripts/phpcs
PHPCBF = ./vendor/squizlabs/php_codesniffer/scripts/phpcbf

default:
	@echo ""
	@echo "Saft - CLI"
	@echo ""
	@echo "- make codebeautifier"
	@echo "- make codesniffer"
	@echo "- make setup-test-environment"
	@echo ""

setup-test-environment:
	cp test/config.yml.dist test/config.yml

codesniffer:
	$(PHPCS) --standard=PSR1,PSR2 --extensions=php -p src/* test/*

codebeautifier:
	$(PHPCBF) --standard=PSR1,PSR2 --extensions=php -p src/* test/*
