#!/bin/bash

# Important: You need to run "make setup-subtrees" once before to ensure

newTag=$1

if [ -z "$newTag" ]; then
    echo "Please provide a tag such as 0.1.42 as first parameter."
    exit 1
fi

typeset -A repos
# core packages
repos[saft.data]=src/Saft/Data
repos[saft.rdf]=src/Saft/Rdf
repos[saft.sparql]=src/Saft/Sparql
repos[saft.store]=src/Saft/Store

# additional packages
repos[saft.arc2]=src/Saft/Addition/ARC2
repos[saft.easyrdf]=src/Saft/Addition/EasyRdf
repos[saft.erfurt]=src/Saft/Addition/Erfurt
repos[saft.querycache]=src/Saft/Addition/QueryCache
repos[saft.redland]=src/Saft/Addition/Redland
repos[saft.skeleton]=src/Saft/Skeleton
repos[saft.store.http]=src/Saft/Addition/HttpStore
repos[saft.store.virtuoso]=src/Saft/Addition/Virtuoso

for i in "${!repos[@]}"
do
    # Access to:
    # $i = branch name
    # ${repos[$i]} = folder path

    # avoid that tags getting used for the wrong repository
    git tag -l | xargs git tag -d

    # put subfolder related changes into its own branch
    git subtree split --prefix=${repos[$i]} -b $i

    # tag that branch with the given tag
    git tag -a $newTag -m "add version tag $newTag" $i

    # push latest state to related remote with tags
    git push -f $i $i:master --tags
done
