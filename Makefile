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

# TODO change this somehow to a loop
push-subtrees:
	git subtree split -P src/Saft/Cache -b saft.cache
	git push saft.cache saft.cache:main
	git subtree split -P src/Saft/Backend/FileCache -b saft.cache.file
	git push saft.cache.file saft.cache.file:main
	git subtree split -P src/Saft/Backend/MemcacheD -b saft.cache.memcached
	git push saft.cache.memcached saft.cache.memcached:main
	git subtree split -P src/Saft/Backend/PhpArrayCache -b saft.cache.phparray
	git push saft.cache.phparray saft.cache.phparray:main
	git subtree split -P src/Saft/Data -b saft.data
	git push saft.data saft.data:main
	git subtree split -P src/Saft/QueryCache -b saft.querycache
	git push saft.querycache saft.querycache:main
	git subtree split -P src/Saft/Rdf -b saft.rdf
	git push saft.rdf saft.rdf:main
	git subtree split -P src/Saft/Sparql -b saft.sparql
	git push saft.sparql saft.sparql:main
	git subtree split -P src/Saft/Store -b saft.store
	git push saft.store saft.store:main
	git subtree split -P src/Saft/Backend/HttpStore -b saft.store.http
	git push saft.store.http saft.store.http:main
	git subtree split -P src/Saft/Backend/Virtuoso -b saft.store.virtuoso
	git push saft.store.virtuoso saft.store.virtuoso:main
