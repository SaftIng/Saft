#
# This file is part of Saft.
#
# (c) Konrad Abicht <hi@inspirito.de>
# (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

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
	@echo "- make apidoc                      - Update API doc."
	@echo "- make cs                 		  - Fixes coding standard issues."
	@echo "- make clean                       - Remove temporary folders and vendor folder."
	@echo "- make commit                      - Runs some quality checks before call git commit."
	@echo "- make remove-subpackage-branches  - Removes subpackage branches."
	@echo "- make remove-subpackage-remotes   - Removes subpackage remotes."
	@echo "- make setup-subpackage-remotes    - Setup subpackage remotes."
	@echo "- make test                        - Run all test suites."
	@echo "- make tag-subpackage-repos        - Adds a new tag to all subpackage repos."
	@echo "- make update-subpackage-repos     - Splits commits and add related to appropriate Saft components."
	@echo ""

apidoc:
	$(SAMI) update -n -v --force $(SAMI-CONFIG)

cs:
	vendor/bin/php-cs-fixer fix

clean:
	rm -r ./gen ./tmp ./vendor

commit:
	make codebeautifier
	make codesniffer
	git-cola

# Remove all subpackages branches
remove-subpackage-branches:
	git branch -D saft.arc2
	git branch -D saft.data
	git branch -D saft.easyrdf
	git branch -D saft.erfurt
	git branch -D saft.hardf
	git branch -D saft.querycache
	git branch -D saft.rdf
	git branch -D saft.redland
	git branch -D saft.sparql
	git branch -D saft.store
	git branch -D saft.store.http
	git branch -D saft.store.virtuoso
	git branch -D saft.test

# Remove all remotes for Saft's subpackages.
remove-subpackage-remotes:
	git remote rm saft.arc2
	git remote rm saft.data
	git remote rm saft.easyrdf
	git remote rm saft.erfurt
	git remote rm saft.hardf
	git remote rm saft.querycache
	git remote rm saft.rdf
	git remote rm saft.redland
	git remote rm saft.sparql
	git remote rm saft.store
	git remote rm saft.store.http
	git remote rm saft.store.virtuoso
	git remote rm saft.test

setup:
	./scripts/setup-components.sh

# Setup all remotes subpackages
setup-subpackage-remotes:
	git remote add saft.arc2 git@github.com:SaftIng/Saft.arc2
	git remote add saft.data git@github.com:SaftIng/Saft.data
	git remote add saft.easyrdf git@github.com:SaftIng/Saft.easyrdf
	git remote add saft.erfurt git@github.com:SaftIng/Saft.erfurt
	git remote add saft.hardf git@github.com:SaftIng/Saft.hardf
	git remote add saft.querycache git@github.com:SaftIng/Saft.querycache
	git remote add saft.rdf git@github.com:SaftIng/Saft.rdf
	git remote add saft.redland git@github.com:SaftIng/Saft.redland
	git remote add saft.sparql git@github.com:SaftIng/Saft.sparql
	git remote add saft.store git@github.com:SaftIng/Saft.store
	git remote add saft.store.http git@github.com:SaftIng/Saft.store.http
	git remote add saft.store.virtuoso git@github.com:SaftIng/Saft.store.virtuoso
	git remote add saft.test git@github.com:SaftIng/Saft.test

tag-subpackage-repos:
	./scripts/tag-subpackage-repos.sh $(TAG)

test:
	./scripts/run-tests.sh

update-subpackage-repos:
	./scripts/update-subpackage-repos.sh
