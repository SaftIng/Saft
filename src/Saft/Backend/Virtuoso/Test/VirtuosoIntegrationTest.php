<?php
namespace Saft\Backend\Virtuoso\Test;

use Saft\Store\Test\SparqlStoreIntegrationTest;
use Saft\Backend\Virtuoso\Store\Virtuoso;

class VirtuosoIntegrationTest extends SparqlStoreIntegrationTest
{
    public function setUp()
    {
        $this->config = $this->getConfigContent();
        
        if (true === isset($this->config['virtuosoConfig'])) {
            $this->fixture = new Virtuoso($this->config['virtuosoConfig']);
        } elseif (true === isset($this->config['configuration']['standardStore'])
            && 'virtuoso' === $this->config['configuration']['standardStore']['type']) {
            $this->fixture = new Virtuoso(
                $this->config['configuration']['standardStore']
            );
        } else {
            $this->markTestSkipped('Array virtuosoConfig is not set in the config.yml.');
        }
        
        $this->className = 'VirtuosoIntegrationTest';
    }
}
