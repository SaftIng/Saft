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
components[erfurt]=src/Saft/Addition/Erfurt
components[httpstore]=src/Saft/Addition/HttpStore
components[querycache]=src/Saft/Addition/QueryCache
components[redland]=src/Saft/Addition/Redland
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
