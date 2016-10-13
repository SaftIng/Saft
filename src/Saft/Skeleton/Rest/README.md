# REST API

It implements the REST interface described [here](http://safting.github.io/doc/restinterface/triplestore/) and  its purpose is to provide a basic middleware, which gets a request object, handles the query and serves a response object in the end. In that way you will be able to integrate it smoothly into different environments.

The REST API is based on [PSR-7](http://www.php-fig.org/psr/psr-7/) and it is using Zends Diactoros implementation internally: https://github.com/zendframework/zend-diactoros

## Basic illustration

```
PSR-7 request
  |
  v
 Hub <--> store (and temp file)
  |
  v
PSR-7 response
```

#### PSR-7 request

The *PSR-7 request* must be an object and represents a client request. Because it is OOP, you can instantiate it and fill it from whereever you are. No need to really provoke a HTTP request to the server using $_GET or something like that.

#### Hub

The Hub is the heart of the API. It will get the request and serves the response in the end. After the request was validated it transforms the given parameters (s, p, o, ...) into a Statement and stuff, to query the store. The answer of the store will be serialized and put into a temporary file.

Example code:

```php
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\NodeUtils;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\BasicTriplePatternStore;

// ...

// init a basic store (it stores its data in the memory
// and provides a basic interface for test usage)
$store = new BasicTriplePatternStore(
    new NodeFactoryImpl(),
    new StatementFactoryImpl(),
    new QueryFactoryImpl(),
    new StatementIteratorFactoryImpl()
);

// init Hub (REST api)
$restHub = new Hub(
    $store,
    new StatementFactoryImpl(),
    new NodeFactoryImpl(),
    new NQuadsSerializerImpl('n-triples'),
    new NodeUtils(new NodeFactoryImpl(), new ParserSerializerUtils())
);

// create PSR-7 request
$request = new ServerRequest(
  // server params
  array('s' => '*', 'p' => '*', 'o' => '*'),
  // uploaded files
  array(),
  // uri
  null,
  // method
  null,
  // body
  'php://input',
  // headers
  array(
      'Accept' => 'application/n-triples,application/json'
  )
);

// compute request and store received PSR-7 response
$response = $restHub->computeRequest($request);

// $response->getBody()->getContents() will provide access to the body of the response
// and may contain something like:
// <http://localhost/Saft/TestGraph/> <http://localhost/Saft/TestGraph/> <http://localhost/Saft/TestGraph/> .
```

#### PSR-7 response

The returned response object represents the answer of the REST api. It comes along with a reference on a stream, which stands for a file or a real stream of string information in general. In our case, it contains the queried serialized statements of the store.
