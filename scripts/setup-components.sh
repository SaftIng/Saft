#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

typeset -A components
# core packages
components[data]=src/Saft/Data
components[rdf]=src/Saft/Rdf
components[sparql]=src/Saft/Sparql
components[store]=src/Saft/Store

# additions
components[arc2]=src/Saft/Addition/ARC2
components[easyrdf]=src/Saft/Addition/EasyRdf
components[hardf]=src/Saft/Addition/hardf
components[httpstore]=src/Saft/Addition/HttpStore
components[virtuoso]=src/Saft/Addition/Virtuoso

for i in "${!components[@]}"
do
    echo ""
    echo "##################################"
    echo "Run composer update for ${components[$i]}"
    echo "##################################"
    echo ""
    cd "$DIR/../${components[$i]}" && composer update
done
