<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Rdf\Test;

use Saft\Rdf\CommonNamespaces;

class CommonNamespacesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new CommonNamespaces();
    }

    /*
     * Tests for extendUri
     */

    public function testExtendUri()
    {
        $this->fixture->add('foo', 'http://foo/');

        $this->assertEquals('http://foo/bar', $this->fixture->extendUri('foo:bar'));
    }

    public function testExtendUriOverlappingPrefixes()
    {
        $this->fixture->add('foo', 'http://foo/');
        $this->fixture->add('foo2', 'http://foo/baz/');

        $this->assertEquals('http://foo/baz/bar', $this->fixture->extendUri('foo2:bar'));
    }

    public function testExtendUriOverlappingPrefixes2()
    {
        // it assumes
        // - ma     => http://www.w3.org/ns/ma-ont#
        // - schema => http://schema.org/
        // are known prefixes.

        $this->assertEquals('http://schema.org/foo', $this->fixture->extendUri('schema:foo'));
    }

    /*
     * Tests for getNamespaces
     */

    public function testGetNamespaces()
    {
        $this->assertTrue(\is_array($this->fixture->getNamespaces()));
        $this->assertEquals(45, \count($this->fixture->getNamespaces()));
    }

    /*
     * Tests for getPrefix
     */

    public function testGetPrefix()
    {
        $this->assertEquals('rdfs', $this->fixture->getPrefix('http://www.w3.org/2000/01/rdf-schema#'));
        $this->assertEquals(null, $this->fixture->getPrefix('http://not-there'));
    }

    /*
     * Tests for getUri
     */

    public function testGetUri()
    {
        $this->assertEquals('http://www.w3.org/2000/01/rdf-schema#', $this->fixture->getUri('rdfs'));
    }

    /*
     * Tests for isShortenedUri
     */

    public function testIsShortenedUri()
    {
        $this->assertTrue($this->fixture->isShortenedUri('rdfs:label'));

        $this->assertFalse($this->fixture->isShortenedUri('http://label'));
    }

    /*
     * Tests for shortenUri
     */

    public function testShortenUri()
    {
        $this->fixture->add('foo', 'http://foo/');

        $this->assertEquals('foo:bar', $this->fixture->shortenUri('http://foo/bar'));
    }

    // test that in case you have overlapping namespace URIs, the longest will be used
    // to avoid results like foo:bar/baz
    public function testShortenUriOverlappingPrefixes()
    {
        $this->fixture->add('foo', 'http://foo/');
        $this->fixture->add('foo2', 'http://foo/baz/');

        $this->assertEquals('foo2:bar', $this->fixture->shortenUri('http://foo/baz/bar'));
    }

    public function testShortenUriNothingFound()
    {
        $this->assertEquals('http://foo/bar', $this->fixture->shortenUri('http://foo/bar'));
    }

    public function testShortenUriTestCache()
    {
        // fresh
        $this->assertEquals([], $this->fixture->getCache());
        $this->assertEquals('rdfs:label', $this->fixture->shortenUri('http://www.w3.org/2000/01/rdf-schema#label'));

        $this->assertEquals(
            [
                'getPrefix_http://www.w3.org/2000/01/rdf-schema#label' => 'rdfs:label'
            ],
            $this->fixture->getCache()
        );

        // cache hits
        $this->assertEquals('rdfs:label', $this->fixture->shortenUri('http://www.w3.org/2000/01/rdf-schema#label'));
        $this->assertEquals('rdfs:label', $this->fixture->shortenUri('http://www.w3.org/2000/01/rdf-schema#label'));
    }
}
