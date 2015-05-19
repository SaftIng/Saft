<?php

namespace Saft\Backend\HttpStore\Test;

use Saft\Backend\HttpStore\Store\Http;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Store\Result\ResultFactoryImpl;
use Saft\Store\Test\StoreAbstractTest;

class HttpAbstractTest extends StoreAbstractTest
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
                $this->config['httpConfig']
            );
        } elseif (true === isset($this->config['configuration']['standardStore'])
            && 'http' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new Http(
                new NodeFactoryImpl(),
                new StatementFactoryImpl(),
                new QueryFactoryImpl(),
                new ResultFactoryImpl(),
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array httpConfig is not set in the config.yml.');
        }

        $this->className = 'HttpIntegrationTest';
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
            $config
        );
    }
}
