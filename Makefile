PHPUNIT = ./vendor/bin/phpunit
PHPCS = ./vendor/bin/phpcs
PHPCBF = ./vendor/bin/phpcbf
SAMI = ./vendor/bin/sami.php
XSLTPROC = xsltproc

PHPCS-RULES = resources/codesniffer-ruleset.xml
SAMI-CONFIG = resources/sami-config.php

default:
	@echo ""
	@echo "Saft - CLI"
	@echo ""
	@echo "- make codebeautifier"
	@echo "- make codesniffer"
	@echo "- make setup-test-environment"
	@echo ""

setup-test-environment:
	cp test-config.yml.dist test-config.yml
	sudo apt-get install xsltproc

test:
	- $(PHPUNIT)
	$(XSLTPROC) -o gen/test/report.html resources/phpunit-results.xsl gen/test/log.junit.xml

codesniffer:
	$(PHPCS) --standard=$(PHPCS-RULES) --extensions=php -p src/*

codebeautifier:
	$(PHPCBF) --standard=$(PHPCS-RULES) --extensions=php -p src/*

apidoc:
	$(SAMI) update -n -v --force $(SAMI-CONFIG)

clean:
	rm -r ./gen ./tmp

commit:
	make codebeautifier
	make codesniffer
	git-cola

mrpropper: clean
	rm -r ./vendor

# TODO change this somehow to a loop
split-subtrees:
	git subtree split -P src/Saft/Cache -b saft.cache
	git subtree split -P src/Saft/Backend/FileCache -b saft.cache.file
	git subtree split -P src/Saft/Backend/MemcacheD -b saft.cache.memcached
	git subtree split -P src/Saft/Data -b saft.data
	git subtree split -P src/Saft/QueryCache -b saft.querycache
	git subtree split -P src/Saft/Rdf -b saft.rdf
	git subtree split -P src/Saft/Sparql -b saft.sparql
	git subtree split -P src/Saft/Store -b saft.store
	git subtree split -P src/Saft/Backend/HttpStore -b saft.store.http
	git subtree split -P src/Saft/Backend/Virtuoso -b saft.store.virtuoso
	git subtree split -P src/Saft/Backend/Redland -b saft.redland

push-subtrees:
	git push saft.cache saft.cache:master
	git push saft.cache.file saft.cache.file:master
	git push saft.cache.memcached saft.cache.memcached:master
	git push saft.data saft.data:master
	git push saft.querycache saft.querycache:master
	git push saft.rdf saft.rdf:master
	git push saft.sparql saft.sparql:master
	git push saft.store saft.store:master
	git push saft.store.http saft.store.http:master
	git push saft.store.virtuoso saft.store.virtuoso:master
	git push saft.redland saft.redland:master
