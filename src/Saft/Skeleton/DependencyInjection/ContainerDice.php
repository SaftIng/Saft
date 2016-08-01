<?php

namespace Saft\Skeleton\DependencyInjection;

use Dice\Dice;

class ContainerDice implements Container
{
    /**
     * @var Dice
     */
    protected $dice;

    /**
     * Creates and returns an instance of a given class name.
     *
     * @param  string $classToInstantiate Name of the class you want to instantiate.
     * @param  array  $parameter          Array which contains all parameter for the class to instantiate.
     *                                    (optional)
     */
    public function createInstanceOf($classToInstantiate, array $parameter = array())
    {
        return $this->dice->create($classToInstantiate, $parameter);
    }

    /**
     * @return Dice|null Instance of Dice, if setup was called, null otherwise.
     */
    public function getDice()
    {
        return $this->dice;
    }

    /**
     * @param array $userDefinedReplacements Array with key-value-pairs. The keys are interfaces you
     *                                       wanna replace with the class, which is the according value.
     */
    public function setup($userDefinedReplacements = array())
    {
        // standard replacements for classes and interfaces
        $replacements = array(
            'Saft\Rdf\AnyPattern' => array('instanceOf' => 'Saft\Rdf\AnyPatternImpl'),
            'Saft\Rdf\BlankNode' => array('instanceOf' => 'Saft\Rdf\BlankNodeImpl'),
            /**
             * Saft\Rdf\Literal uses a couple of constructor parameters which are interfaces, so
             * we have to the according classes here.
             */
            'Saft\Rdf\Literal' => array(
                'instanceOf' => 'Saft\Rdf\LiteralImpl',
                'constructParams' => array(
                    // define second parameter which is of type NamedNode
                    new \Saft\Rdf\NamedNodeImpl('http://www.w3.org/2001/XMLSchema#string')
                )
            ),
            'Saft\Rdf\NamedNode' => array('instanceOf' => 'Saft\Rdf\NamedNodeImpl'),
            'Saft\Rdf\NodeFactory' => array('instanceOf' => 'Saft\Rdf\NodeFactoryImpl'),
            'Saft\Rdf\StatementFactory' => array('instanceOf' => 'Saft\Rdf\StatementFactoryImpl'),
            'Saft\Rdf\StatementIteratorFactory' => array('instanceOf' => 'Saft\Rdf\StatementIteratorFactoryImpl'),
            'Saft\Sparql\Query\QueryFactory' => array('instanceOf' => 'Saft\Sparql\Query\QueryFactoryImpl'),
            'Saft\Sparql\Result\ResultFactory' => array('instanceOf' => 'Saft\Sparql\Result\ResultFactoryImpl'),
        );

        $this->dice = new Dice();

        foreach ($replacements as $interfaceOrClass => $rule) {
            // is current class or interface defined by user? if so, use his version
            if (isset($userDefinedReplacements[$interfaceOrClass])) {
                $rule = $userDefinedReplacements[$interfaceOrClass];
            // if not, use standard one
            } else {
                $rule = $replacements[$interfaceOrClass];
            }

            $this->dice->addRule($interfaceOrClass, $rule);
        }
    }
}
