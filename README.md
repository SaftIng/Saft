# Saft

Saft stands for **S**emantic **A**pplication **F**ramework Saf**t** and is a PHP framework, which provides RDF handling and support for Semantic Web technologies. It consists of the core library (_Saft.data_, _Saft.rdf_, _Saft.sparql_ and _Saft.store_) and some additions (e.g. adapter for triple stores or integration of libraries like ARC2 and EasyRdf), which extend the core with application specific code.

There are currently 4 other RDF-libraries for PHP available (EasyRdf, Erfurt, hardf, ARC2). Each implements different areas with various quality and feature-coverage. Combined, they provide a rich feature-set from RDF data handling, serialization and parsing to database access. With Saft we aim to provide an integration layer to enable the usage of most of these libraries at the same time.

### Build status and code coverage

| Core Component | Composer Package | Build Status                                                                                                              | Code Coverage                                                                                                                                                      |
|:---------------|:-----------|:---------------------------------------------------------------------------------------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Data        | saft/saft-data    | [![Build Status](https://travis-ci.org/SaftIng/Saft.data.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.data)     | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.data/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.data?branch=master)     |
| Rdf         | saft/saft-rdf   | [![Build Status](https://travis-ci.org/SaftIng/Saft.rdf.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.rdf)       | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.rdf/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.rdf?branch=master)       |
| Sparql      | saft/saft-sparql   | [![Build Status](https://travis-ci.org/SaftIng/Saft.sparql.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.sparql) | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.sparql/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.sparql?branch=master) |
| Store       | saft/saft-store   | [![Build Status](https://travis-ci.org/SaftIng/Saft.store.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.store)   | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.store/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.store?branch=master)   |

| Addition | Composer Package | Build Status                                                                                                                              | Code Coverage                                                                                                                                                                      |
|:---------|:----|:--------------------------------------------------------------------------------------------------------------------------------------|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| ARC2   | saft/saft-arc2   | [![Build Status](https://travis-ci.org/SaftIng/Saft.arc2.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.arc2)                     | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.arc2/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.arc2?branch=master)                     |
| hardf  | saft/saft-hardf   | [![Build Status](https://travis-ci.org/SaftIng/Saft.hardf.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.hardf)                   | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.hardf/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.hardf?branch=master)                   |
| Virtuoso | saft/saft-store-virtuoso | [![Build Status](https://travis-ci.org/SaftIng/Saft.store.virtuoso.svg?branch=master)](https://travis-ci.org/SaftIng/Saft.store.virtuoso) | [![Coverage Status](https://coveralls.io/repos/github/SaftIng/Saft.store.virtuoso/badge.svg?branch=master)](https://coveralls.io/github/SaftIng/Saft.store.virtuoso?branch=master) |

## License

Copyright (C) 2017 by Konrad Abicht, Natanael Arndt and the individual [contributors](CONTRIBUTORS)

This program is licensed under the terms of the [MIT license](https://github.com/SaftIng/Saft/blob/master/LICENSE).

## Current development status

Saft provides (basic) support for the following RDF libraries for PHP:

* ARC2 (currently only data storage)
* EasyRDF (currently only parser and serializer)
* hardf (parser and serializer)
