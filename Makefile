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
	@echo "- make split-subtrees - Setup test-environment of Saft."
	@echo "- make push-subtrees - Push to subtree repos."
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

# Setup all remotes for Saft's subtree repositories.
setup-subtrees:
	- git remote add saft.data git@github.com:SaftIng/Saft.data
	- git remote add saft.easyrdf git@github.com:SaftIng/Saft.easyrdf
	- git remote add saft.querycache git@github.com:SaftIng/Saft.querycache
	- git remote add saft.rdf git@github.com:SaftIng/Saft.rdf
	- git remote add saft.redland git@github.com:SaftIng/Saft.redland
	- git remote add saft.sparql git@github.com:SaftIng/Saft.sparql
	- git remote add saft.store git@github.com:SaftIng/Saft.store
	- git remote add saft.store.http git@github.com:SaftIng/Saft.store.http
	- git remote add saft.store.virtuoso git@github.com:SaftIng/Saft.store.virtuoso

# TODO change this somehow to a loop
split-subtrees:
	git subtree split -P src/Saft/Addition/HttpStore -b saft.store.http
	git subtree split -P src/Saft/Addition/EasyRdf -b saft.easyrdf
	git subtree split -P src/Saft/Addition/QueryCache -b saft.querycache
	git subtree split -P src/Saft/Addition/Redland -b saft.redland
	git subtree split -P src/Saft/Addition/Virtuoso -b saft.store.virtuoso
	git subtree split -P src/Saft/Data -b saft.data
	git subtree split -P src/Saft/Rdf -b saft.rdf
	git subtree split -P src/Saft/Sparql -b saft.sparql
	git subtree split -P src/Saft/Store -b saft.store

# After the call of make split-subtrees, that command pushes all the new changes to the according remotes.
push-subtrees:
	git push saft.data saft.data:master
	git push saft.easyrdf saft.easyrdf:master
	git push saft.querycache saft.querycache:master
	git push saft.rdf saft.rdf:master
	git push saft.redland saft.redland:master
	git push saft.sparql saft.sparql:master
	git push saft.store saft.store:master
	git push saft.store.http saft.store.http:master
	git push saft.store.virtuoso saft.store.virtuoso:master
