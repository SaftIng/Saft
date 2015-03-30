<?php
namespace Saft\Backend\HttpStore\Test;

use Saft\Rdf\ArrayStatementIteratorImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\StatementImpl;
use Symfony\Component\Yaml\Parser;
use Saft\Cache\Cache;
use Saft\Backend\HttpStore\Store\Http;

class ClientUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests existence (simple)
     */
    public function testExistence()
    {
        $this->assertTrue(class_exists('\Saft\Backend\HttpStore\Net\Client'));
    }
}
