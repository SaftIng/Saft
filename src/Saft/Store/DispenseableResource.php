<?php

namespace Saft\Store;

/**
 * A dispenseable resource is a resource, which has no certain semantic and is
 * used to be used for rapid prototyping.
 */
class DispenseableResource
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var \Enable\Store\Graph
     */
    protected $graph = null;

    /**
     * @var \Enable\Namespace
     */
    protected $namespaces = null;

    /**
     * Prefix to put on the start of each URI
     * @var string
     */
    protected $uriPrefix = "http://localhost/";

    /**
     *
     * @param \Enable\Store\Graph $graph Instance of an initialized graph
     * @return void
     */
    public function __construct(\Saft\Store\Graph $graph)
    {
        $this->graph = $graph;
    }

    /**
     *
     * @param string $name Name of the field
     * @return mixed Value to according $name-field.
     * @throw
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     *
     * @param string $name Name of the field
     * @return boolean True, if the field has a value, false otherwise.
     */
    public function __isset($name)
    {
        return isset($this->data[urlencode($name)]);
    }

    /**
     *
     * @param string $name  Name of the field
     * @param mixed  $value Value of the field
     * @return
     * @throw
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        // adapt URI if the id was changed
        if ("id" == $name) {
            $this->uri = $this->uriPrefix . urlencode($this->id) . "/";
        }
    }

    /**
     * Adds a new namespace to the pool, which will be considered later on when
     * processing URI.
     *
     * @param  string $prefix
     * @param  string $uri
     * @return void
     */
    public function addNamespace($prefix, $uri)
    {
        \EasyRdf\RdfNamespace::set($prefix, $uri);
    }

    /**
     * Delete an existing namespace from the global registry.
     *
     * @param  string $prefix Prefix of the namespace to delete.
     * @return void
     */
    public function deleteNamespace($prefix)
    {
        \EasyRdf\RdfNamespace::delete($prefix);
    }

    /**
     * @return array Array of triples based on saved data.
     */
    public function generateTriples()
    {
        $triples = array();

        // rdf:resource
        $triples[] = array(
            $this->uri,
            "a",
            array(
                "type" => "uri",
                "value" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#resource"
            )
        );

        foreach ($this->data as $property => $value) {
            // ignore system properties
            if (true == in_array($property, array("id", "uri"))) {
                continue;
            }

            $predicate = "";

            // if the property is a prefixed URI
            if (false !== strpos($property, ":")) {
                $predicate = \EasyRdf\RdfNamespace::expand($property);

                if ($predicate == $property) {
                    throw new \Exception(
                        "Can't expand shortened URI, you have register all required ".
                        "namespaces and prefixes before save or load."
                    );
                }
            } else {
                $predicate = $this->uri . urlencode($property);
            }

            // add a new triple
            $triples[] = array(
                // subject
                $this->uri,
                // predicate
                $predicate,
                // object
                array(
                    "type" => false == \Saft\Rdf\NamedNode::check($value) ? "literal" : "uri",
                    "value" => $value
                )
            );
        }

        return $triples;
    }

    /**
     * @return array Array containing previously saved key-value pairs for this
     *               resource.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Init this instance, such as generate an unique id and URI.
     *
     * @param  string $id ID for this resource.
     * @return void
     */
    public function init($id = "", array $options = array())
    {
        if (true === empty($id)) {
            $id = rand(0, time()) * microtime();
        }

        // unique identifier
        $this->id = hash("sha1", $id);

        // URI to identify (load, save) this resource later on
        $this->uri = $this->uriPrefix . urlencode($this->id) . "/";
    }

    /**
     * Loads a certain resource from the store.
     *
     * @param  string $id Previously used string to identify this resource.
     * @return array Array containing previously saved key-value pairs for this
     *               resource.
     */
    public function load($id)
    {
        $this->reset();

        $uri = $this->uriPrefix . urlencode($id) . "/";

        // get all information about a certain resource
        $resourceInformation = $this->graph->getResourceInformation($uri);

        foreach ($resourceInformation as $entry) {
            $property = $entry[1];

            // if the property is a prefixed URI
            if (false !== strpos($entry[1], ":")) {
                $property = \EasyRdf\RdfNamespace::shorten($entry[1]);

                // if the shortening was not possible, but the property URI contains
                // the URI of this resource than remove this URI part and let only
                // the property be (thats the case if the user saves directly the property
                // such as "$r->property1", so it will be saved as $r-URI/property1)
                if (null === $property) {
                    $prefix = $this->uriPrefix . $this->id . "/";

                    if (false !== strpos($entry[1], $prefix)) {
                        $property = str_replace($prefix, "", $entry[1]);
                    }
                }
            }

            $this->data[$property] = $entry[2]["value"];
        }
    }

    /**
     * Removes all data from this instance, but no changes in the store! It only
     * keeps property id and uri.
     *
     * @return void
     */
    public function reset()
    {
        $this->data = array("id" => $this->id, "uri" => $this->uri);
    }

    /**
     * Save information about the resource in a certain graph.
     *
     * @return void
     * @throw  \Enable\Exception
     */
    public function save()
    {
        $triplesToAdd = $this->generateTriples();

        // get all information about a certain resource
        $resourceInformation = $this->graph->getResourceInformation($this->uri);

        // if this resource was created before remove all old resource triples
        if (null !== $resourceInformation) {
            $this->graph->dropMultipleTriples($resourceInformation);
        }

        $this->graph->addMultipleTriples($triplesToAdd);
    }
}
