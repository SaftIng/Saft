#!/bin/bash

echo $TRAVIS_PHP_VERSION

# skip hhvm
if [[ $TRAVIS_PHP_VERSION = "hhv"* ]]; then
    exit 0
fi

# get build dependencies
sudo apt-get install -y unixODBC-dev

PHPVERSION=$( php -v | head -n1 | sed "s|^PHP \([0-9][0-9\.]*\).*$|\1|" | tr -d '\n' )

ls ~/.phpenv/versions/
echo "PHPVERSION: " $PHPVERSION
echo "LOADED CONFIG: " `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

# get php sources
wget https://github.com/php/php-src/archive/php-$PHPVERSION.tar.gz
ls
tar -xzf php-$PHPVERSION.tar.gz

# build odbc extension
cd php-src-php-$PHPVERSION/ext/odbc/
phpize
# use fix from https://github.com/docker-library/php/issues/103
sed -ri 's@^ *test +"\$PHP_.*" *= *"no" *&& *PHP_.*=yes *$@#&@g' configure
./configure --with-unixODBC=shared,/usr
make
make install

# enable odbc
echo "extension=odbc.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

# build pdo_odbc
cd ../pdo_odbc/
phpize
./configure --with-pdo-odbc=unixODBC,/usr
make
make install

#enable pdo_odbc
echo "extension=pdo_odbc.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
php -m

# build redland
cd ../../../
# swig is required by the redland-bindings, gtk-doc-tools is required by raptor and librdf, bison is required to build raptor and rasqal
sudo apt-get -y install swig bison gtk-doc-tools #librasqal3-dev
git clone git://github.com/dajobe/rasqal.git
git clone git://github.com/dajobe/raptor.git
git clone git://github.com/dajobe/librdf.git
git clone git://github.com/dajobe/redland-bindings.git

cd rasqal
./autogen.sh
make
sudo make install

cd ../raptor
./autogen.sh --enable-gtk-doc-html=no
make
sudo make install

cd ../librdf --enable-gtk-doc-html=no
./autogen.sh
make
sudo make install

cd ../redland-bindings
./autogen.sh --with-php
make
make install

#enable redland
echo "extension=redland.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
php -m
