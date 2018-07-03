#!/bin/bash
set -ex

#
# Setup Virtuoso Docker container
#
# using SPARQL_UPDATE=true to allow write operations via SPARQL endpoint without authentication.
#

docker pull tenforce/virtuoso:1.1.1-virtuoso7.2.4
docker run -it --name=virtuoso \
    -p 8890:8890 \
    -e DBA_PASSWORD=dba \
    -e SPARQL_UPDATE=true \
    -d tenforce/virtuoso:1.1.1-virtuoso7.2.4
