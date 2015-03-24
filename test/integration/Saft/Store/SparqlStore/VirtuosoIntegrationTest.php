<?php
namespace Saft\Store\SparqlStore;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\VariableImpl;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;

class VirtuosoIntegrationTest extends SparqlStoreIntegrationTest
{
    public function setUp()
    {
        $this->config = $this->getConfigContent();
        
        if (true === isset($this->config['virtuosoConfig'])) {
            $this->fixture = new \Saft\Store\SparqlStore\Virtuoso($this->config['virtuosoConfig']);
        } elseif (true === isset($this->config['configuration']['standardStore'])
            && 'virtuoso' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new \Saft\Store\SparqlStore\Virtuoso(
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
        }
        
        $this->className = 'VirtuosoIntegrationTest';
    }
}
