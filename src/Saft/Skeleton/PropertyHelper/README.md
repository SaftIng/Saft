# PropertyHelper

## About

The purpose of a property helper is to provide useful methods to handle properties and resources with certain properties.

Think about titles: A title helper is helpful in cases where you want the title of all your resources.

This implementation provides a basic index, which caches title information for each related resource. You need to create an index first, using a key-value-pair based caching solution. After you created it, you can ask the index to give you titles for different lists of URIs.

The way this PropertyHelper is implemented helps you to integrate it in different scenarios. Our main approach would be to use it inside an endpoint, which receives an URI list and responses with a list of titles, if available.

Using RequestHandler makes it easy to deploy a PropertyHelper installation, because it hides all the hassle you need to setup and init the cache, store, ... . Just instantiate it, set it up and it's ready for use.

### Cache backends

We use [Zend Cache](https://github.com/zendframework/zend-cache/) to provide a key-value pair based caching infrastructure. If you want to use certain cache, please consider installing PHP-extension and cache-backend by yourself.

Currently the following backends are supported:
* APC/APCu
* MemcacheD
* Memory (PHP-array, only for tests)
* MongoDB
* Redis

## How to use

Please have a look into prepared [examples](https://github.com/SaftIng/Saft.example/tree/master/PropertyHelper).

## Credits

Based on the work of [Simeon Ackermann](imeonackermann).
