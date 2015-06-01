<?php

namespace Saft\Backend\HttpStore\Test;

use Saft\Backend\HttpStore\Store\Http;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;

class HttpTest extends StoreAbstractTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadTestConfiguration();

        if (true === isset($this->config['httpConfig'])) {
            $this->fixture = new Http(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                new StatementIteratorFactoryImpl(),
                $this->config['httpConfig']
            );

        } else {
            $this->markTestSkipped('Array httpConfig is not set in the config.yml.');
        }
    }

    /**
     * Tests openConnection
     */

    public function testOpenConnectionInvalidUrl()
    {
        // We expect that authentication fails, because the auth url is not valid
        $this->setExpectedException('\Exception');

        $config = array('authUrl' => 'http://not existend');
        new Http(
            new NodeFactoryImpl(),
            new StatementFactoryImpl(),
            new QueryFactoryImpl(),
            new ResultFactoryImpl(),
            new StatementIteratorFactoryImpl(),
            $config
        );
    }
}
