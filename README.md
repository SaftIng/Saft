# Saft

#### About

Saft stands for **S**emantic **A**pplication **F**ramework Saf**t** and is a PHP framework, which provides RDF handling and support for Semantic Web technologies. It consists of the core library (RDF, Data, Sparql and Store) and additions (e.g. adapter for triple stores or integration of libraries like ARC2 and hardf). An addition uses the core library and provides an adapter for a certain application-specific system. We use additions to integrate foreign libraries.

#### Why another PHP library?

Due the lack of a major library written in PHP, like Jena in Java, we decided to provide an integration layer for all existing libraries. These are ARC2, EasyRdf, hardf and Erfurt. Each has its own strength, but also lacks certain functionality. The idea with Saft was, to seamlessly integrate required libraries and cherry pick what you need from them.

## Core components

| Core Component | Composer Package | Info                           |
|:---------------|:-----------------|:-------------------------------|
| Rdf            | saft/saft-rdf    | Major RDF entities             |
| Data           | saft/saft-data   | Serialisation and Parsing      |
| Sparql         | saft/saft-sparql | SPARQL results (Store related) |
| Store          | saft/saft-store  | RDF Store related              |

Each core components provides major functionality for a certain area. The idea is to provide interfaces and for each interface a standard implementation. Use our implementation or write your own, based on our interfaces. This allows the integration of your code into our infrastructure. For instance: Use the hardf addition to parse files and load RDF in a store, via an adapter of yours.

## Additions

| Addition   | Composer Package         | Info                                          |
|:-----------|:-------------------------|:----------------------------------------------|
| ARC2       | saft/saft-arc2           | Integrates ARC2's RDF Store                   |
| hardf      | saft/saft-hardf          | Integrates hardf parser and serializer parser |
| Http Store | saft/saft-store-http     | Enables you to query SPARQL endpoints.        |
| Virtuoso   | saft/saft-store-virtuoso | Connection to Virtuoso Server¹ via ODBC       |

¹ https://virtuoso.openlinksw.com

Additions use the core library and add further functionality. This approach allows us to be flexible and integrate foreign code by keeping a certain amount of semantic stability. Also, you can combine additions and add your own code. For instance, use hardf to parse RDF files and store RDF using the Virtuoso addition.

## License

Copyright (C) 2017 by Konrad Abicht, Natanael Arndt and the individual [contributors](CONTRIBUTORS). This program is licensed under the terms of the [MIT license](https://github.com/SaftIng/Saft/blob/master/LICENSE).
