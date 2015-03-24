<?php
namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;

class HttpIntegrationTest extends SparqlStoreIntegrationTest
{
    public function setUp()
    {
        $this->config = $this->getConfigContent();
        
        if (true === isset($this->config['httpConfig'])) {
            $this->fixture = new Http($this->config['httpConfig']);
        } elseif (true === isset($this->config['configuration']['standardStore'])
            && 'http' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new Http(
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array httpConfig is not set in the config.yml.');
        }
        
        $this->className = 'HttpIntegrationTest';
    }
}
