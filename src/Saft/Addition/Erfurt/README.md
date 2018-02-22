# Saft.erfurt

[READ-ONLY] _Saft.erfurt subtree of the _Saft_ project.

That package integrates the [Erfurt](https://github.com/AKSW/Erfurt) project into Saft.

## Query Cache

It currently supports read-only queries on the datastore, which means that, if you change something in the store, you MUST invalidate the whole cache in order to get valid results for your queries later on.
