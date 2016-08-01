<?php

namespace Saft\Skeleton\Data;

use Saft\Addition\EasyRdf\Data\SerializerFactoryEasyRdf;
use Saft\Data\SerializerFactoryImpl;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\StatementFactory;

/**
 * This factory creates the most suitable serialization instance for a given serialization.
 */
class SerializerFactory
{
    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * @param NodeFactory $nodeFactory
     * @param StatementFactory $statementFactory
     */
    public function __construct(NodeFactory $nodeFactory, StatementFactory $statementFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->statementFactory = $statementFactory;
    }

    /**
     * @param string $serialization
     */
    public function createSerializerFor($serialization)
    {
        $serializerFactoryImpl = new SerializerFactoryImpl($this->nodeFactory, $this->statementFactory);

        // try Saft's own implementation first
        if (in_array($serialization, $serializerFactoryImpl->getSupportedSerializations())) {
            return $serializerFactoryImpl->createSerializerFor($serialization);

        // try EasyRdf next
        } else {
            $serializerFactoryEasyRdf = new SerializerFactoryEasyRdf();

            if (in_array($serialization, $serializerFactoryEasyRdf->getSupportedSerializations())) {
                return $serializerFactoryEasyRdf->createSerializerFor($serialization);
            }
        }

        return null;
    }
}
