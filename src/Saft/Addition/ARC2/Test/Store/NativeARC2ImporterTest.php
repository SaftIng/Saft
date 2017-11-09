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

namespace Saft\Addition\ARC2\Test\Store;

use Saft\Addition\ARC2\Store\NativeARC2Importer;
use Saft\Test\TestCase;

class NativeHardfToARC2ImporterTest extends TestCase
{
    protected $arc2Store;

    public function setUp()
    {
        parent::setUp();

        if (defined('IN_TRAVIS')) {
            $this->loadTestConfiguration(__DIR__ .'/../../test-config-travis.yml');
        } else {
            $this->loadTestConfiguration(__DIR__ .'/../../test-config.yml');
        }

        $this->arc2Store = \ARC2::getStore(array(
            'db_host' => $this->configuration['arc2Config']['host'],
            'db_name' => $this->configuration['arc2Config']['database'],
            'db_user' => $this->configuration['arc2Config']['username'],
            'db_pwd' => $this->configuration['arc2Config']['password'],
            'store_name' => 'saft_'
        ));

        if (!$this->arc2Store->isSetUp()) {
           $this->arc2Store->setUp();
        }

        $this->fixture = new NativeARC2Importer($this->arc2Store);
    }

    /*
     * Tests for importN3FileIntoGraph
     */

    public function testImportN3FileIntoGraph()
    {
        $this->arc2Store->query('DELETE FROM <'. $this->testGraph .'>');
        $res = $this->arc2Store->query('SELECT * FROM <'. $this->testGraph .'> WHERE { ?s ?p ?o. } ');
        $this->assertEquals(0, count($res['result']['rows']));

        $this->fixture->importN3FileIntoGraph(
            __DIR__ . '/../resources/example-resources.n3',
            $this->testGraph->getUri()
        );

        $res = $this->arc2Store->query('SELECT * FROM <'. $this->testGraph .'> WHERE { ?s ?p ?o. } ');
        $this->assertEquals(442, count($res['result']['rows']));
    }
}
