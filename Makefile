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
	@echo "You can execute:"
	@echo "- make codebeautifier - Clean and format code."
	@echo "- make codesniffer - Check code format."
	@echo "- make setup-test-environment - Setup test-environment of Saft."
	@echo "- make setup-subtrees - Setup all remotes for Saft's subtree repositories."
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

# Remove all remotes for Saft's subpackages.
remove-subpackage-remotes:
	git remote rm saft.arc2
	git remote rm saft.data
	git remote rm saft.easyrdf
	git remote rm saft.erfurt
	git remote rm saft.querycache
	git remote rm saft.rdf
	git remote rm saft.redland
	git remote rm saft.rest
	git remote rm saft.skeleton
	git remote rm saft.sparql
	git remote rm saft.store
	git remote rm saft.store.http
	git remote rm saft.store.virtuoso
	git remote rm saft.test

# Setup all remotes subpackages
setup-subpackage-remotes:
	git remote add saft.arc2 git@github.com:SaftIng/Saft.arc2
	git remote add saft.data git@github.com:SaftIng/Saft.data
	git remote add saft.easyrdf git@github.com:SaftIng/Saft.easyrdf
	git remote add saft.erfurt git@github.com:SaftIng/Saft.erfurt
	git remote add saft.querycache git@github.com:SaftIng/Saft.querycache
	git remote add saft.rdf git@github.com:SaftIng/Saft.rdf
	git remote add saft.redland git@github.com:SaftIng/Saft.redland
	git remote add saft.rest git@github.com:SaftIng/Saft.rest
	git remote add saft.skeleton git@github.com:SaftIng/Saft.skeleton
	git remote add saft.sparql git@github.com:SaftIng/Saft.sparql
	git remote add saft.store git@github.com:SaftIng/Saft.store
	git remote add saft.store.http git@github.com:SaftIng/Saft.store.http
	git remote add saft.store.virtuoso git@github.com:SaftIng/Saft.store.virtuoso
	git remote add saft.test git@github.com:SaftIng/Saft.test
