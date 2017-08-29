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

namespace Saft\Addition\Redland\Rdf;

use Saft\Rdf\AbstractLiteral;
use Saft\Rdf\NodeUtils;

class Literal extends AbstractLiteral
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    protected static $xsdString = 'http://www.w3.org/2001/XMLSchema#string';
    protected static $rdfLangString = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#langString';

    /**
     * @var librdf_node the wrapped redland node
     */
    protected $redlandNode;

    /**
     * @param ? $redlandNode
     * @param NodeFactory $nodeFactory
     * @param NodeUtils $nodeUtils
     */
    public function __construct($redlandNode, NodeFactory $nodeFactory, NodeUtils $nodeUtils)
    {
        if ($redlandNode === null) {
            throw new \Exception('Can\'t initialize literal with null.');
        }

        if (!gettype($redlandNode) == 'resource' || !get_resource_type($redlandNode) == '_p_librdf_node_s') {
            throw new \Exception('Redland Literals have to be initialized with a Redland node.');
        }

        $this->nodeFactory = $nodeFactory;
        $this->redlandNode = $redlandNode;

        parent::__construct($nodeUtils);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return librdf_node_get_literal_value($this->redlandNode);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getDatatype()
    {
        $datatypeUri = self::$xsdString;
        $datatype = librdf_node_get_literal_value_datatype_uri($this->redlandNode);
        if ($datatype !== null) {
            $datatypeUri = librdf_uri_to_string($datatype);
        } elseif ($this->getLanguage() !== null) {
            $datatypeUri = self::$rdfLangString;
        }

        return $this->nodeFactory->createNamedNode($datatypeUri);
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return librdf_node_get_literal_value_language($this->redlandNode);
    }

    public function getRedlandNode()
    {
        return $this->redlandNode;
    }
}
