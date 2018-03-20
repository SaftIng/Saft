#!/bin/bash

#
# This file is part of Saft.
#
# (c) Konrad Abicht <hi@inspirito.de>
# (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

set -ex
BASE_PATH=$(pwd)
E_UNREACHABLE=86

# skip hhvm
if [[ $TRAVIS_PHP_VERSION = "hhv"* ]]; then
    exit 0
fi

# We build all Virtuoso version from scratch
if [[ "$VIRTUOSO" != "" ]]
then
    sudo apt-get update -qq

    #
    # virtuoso 7.2.4.2
    #
    if [[ "$VIRTUOSO" == "7.2.4.2" ]]
    then
        sudo apt-get install libssl-dev -q
        sudo apt-get install autoconf automake bison flex gawk gperf libtool -q

        if [[ -f virtuoso-opensource/$VIRTUOSO/binsrc/virtuoso/virtuoso-t ]]
        then
            echo "use cached virtuoso-opensource"
            cd virtuoso-opensource/$VIRTUOSO
        else
            wget --no-check-certificate -q https://github.com/openlink/virtuoso-opensource/archive/v$VIRTUOSO.zip -O virtuoso-opensource.zip

            unzip -q virtuoso-opensource.zip
            rm -r virtuoso-opensource/$VIRTUOSO || true
            mv virtuoso-opensource-$VIRTUOSO virtuoso-opensource/$VIRTUOSO

            cd virtuoso-opensource/$VIRTUOSO
            ./autogen.sh

            ./configure --program-transform-name="s/isql/isql-v/" --with-readline --disable-all-vads |& tee #configure.log

            # Only output error and warnings
            make > /dev/null
        fi

        sudo make install

        sudo /usr/local/virtuoso-opensource/bin/virtuoso-t -f -c /usr/local/virtuoso-opensource/var/lib/virtuoso/db/virtuoso.ini &

        sleep 15

        sudo /usr/local/virtuoso-opensource/bin/isql-v 1111 dba dba $BASE_PATH/scripts/virtuoso-sparql-permission.sql
    fi

    # configure datasource name for ODBC connection
    echo "[VOS]" | sudo tee -a /etc/odbc.ini > /dev/null
    echo "Driver=/usr/local/virtuoso-opensource/lib/virtodbc.so" | sudo tee -a /etc/odbc.ini > /dev/null
    echo "Description=Virtuoso OpenSource Edition" | sudo tee -a /etc/odbc.ini > /dev/null
    echo "Address=localhost:1111" | sudo tee -a /etc/odbc.ini > /dev/null
fi
