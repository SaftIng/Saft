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
use Saft\Test\TestCase;

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
}
