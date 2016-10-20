# Store API

## FileImporter

It helps you to import RDF files into your store. Currently, its still unstable.

Short example for a Virtuoso store:

```php
$virtuosoStore = new Virtuoso(
    new NodeFactoryImpl(new NodeUtils()),
    new StatementFactoryImpl(),
    new QueryFactoryImpl(new NodeUtils(), new QueryUtils()),
    new ResultFactoryImpl(),
    new StatementIteratorFactoryImpl(),
    array(/* dsn, user, pass, ... */)
)
$importer = new FileImporter($virtuosoStore);
$importer->importFile(
    '/tmp/file-to-import.ttl',
    new NamedNodeImpl(new NodeUtils(), 'http://graph/to/import/data/into'),
    10, // batch size; depending on your system smaller or higher values may result in better performance
);
```

The method `importFile` may run a while, depending on its size. When you set the max execution time for PHP CLI high enough, you can even import files larger than 1 GB, which is not recommended. If you have such huge files, try the import utils of your triple store.
