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

namespace Saft\Rdf;

class NodeFactoryImpl implements NodeFactory
{
    /**
     * @param CommonNamespaces $commonNamespaces if null, CommonNamespaces will used automatically
     */
    public function __construct(CommonNamespaces $commonNamespaces = null)
    {
        if (null == $commonNamespaces) {
            $this->commonNamespaces = new CommonNamespaces();
        } else {
            $this->commonNamespaces = $commonNamespaces;
        }
    }

    /**
     * @param string      $value
     * @param Node|string $datatype (optional)
     * @param string      $lang     (optional)
     *
     * @return Literal
     */
    public function createLiteral($value, $datatype = null, $lang = null): Literal
    {
        if ($datatype !== null) {
            if (!$datatype instanceof Node) {
                $datatype = $this->createNamedNode($datatype);
            } elseif (!$datatype->isNamed()) {
                throw new \Exception('Argument datatype has to be a named node.');
            }
        }

        return new LiteralImpl($value, $datatype, $lang);
    }

    /*
     * @return NamedNode
     */
    public function createNamedNode($uri): NamedNode
    {
        return new NamedNodeImpl($this->commonNamespaces->extendUri($uri));
    }

    /*
     * @return BlankNode
     */
    public function createBlankNode($blankId): BlankNode
    {
        return new BlankNodeImpl($blankId);
    }

    /*
     * @return AnyPattern
     */
    public function createAnyPattern(): AnyPattern
    {
        return new AnyPatternImpl();
    }
}
