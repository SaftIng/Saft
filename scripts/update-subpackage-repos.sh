#!/bin/bash

# Important: You need to run "make setup-subtrees" once before to ensure

typeset -A repos
# core packages
repos[saft.data]=src/Saft/Data
repos[saft.rdf]=src/Saft/Rdf
repos[saft.sparql]=src/Saft/Sparql
repos[saft.store]=src/Saft/Store

# additional packages
repos[saft.arc2]=src/Saft/Addition/ARC2
repos[saft.easyrdf]=src/Saft/Addition/EasyRdf
repos[saft.hardf]=src/Saft/Addition/hardf
repos[saft.store.http]=src/Saft/Addition/HttpStore
repos[saft.store.virtuoso]=src/Saft/Addition/Virtuoso

for i in "${!repos[@]}"
do
    # Access to:
    # $i = branch name
    # ${repos[$i]} = folder path

    echo ""
    echo "Update master branch for ${repos[$i]}"

    # avoid that tags getting used for the wrong repository
    git tag -l | xargs git tag -d

    # put subfolder related changes into its own branch
    git subtree split --prefix=${repos[$i]} -b $i

    # push latest state to related remote with tags
    git push -f $i $i:master
done
