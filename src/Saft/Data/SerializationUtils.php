<?php

namespace Saft\Data;

class SerializationUtils
{
    /**
     * @var
     */
    protected $serializationMimeTypeMap = array();

    public function __construct()
    {
        $this->serializationMimeTypeMap = array(
            'json-ld' => 'application/json',
            'n-quads' => 'application/n-quads',
            'n-triples' => 'application/n-triples',
            'rdf-json' => 'application/json',
            'rdf-xml' => 'application/rdf+xml',
            'rdfa' => 'text/html',
            'trig' => 'application/trig',
            'turtle' => 'text/turtle'
        );
    }

    /**
     * Returns serialization pendant for a given MIME type, if available.
     *
     * @param  string $mime
     * @return string MIME-Type, if available, null otherwise.
     */
    public function mimeToSerialization($mime)
    {
        foreach ($this->serializationMimeTypeMap as $serialization => $mimePendant) {
            if ($mime == $mimePendant) {
                return $serialization;
            }
        }

        return null;
    }

    /**
     * Returns MIME type pendant for a given serialization, if available.
     *
     * @param  string $serialization
     * @return string MIME-Type, if available, null otherwise.
     */
    public function serializationToMime($serialization)
    {
        if (isset($this->serializationMimeTypeMap[$serialization])) {
            return $this->serializationMimeTypeMap[$serialization];
        }

        return null;
    }
}
