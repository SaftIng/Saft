<?php
namespace Saft\Data;

use Saft\Rdf\StatementIterator;

class NQuadsSerializerImpl implements SerializerInterface
{

    /**
     * Prefixes are ignored in the NQuads serialization
     * @param $prefixes array will be ignored
     * @return void
     */
    public function setPrefixes(array $prefixes)
    {
    }

    /**
     * @unstable
     * @param $outputStream string filename of the stream to where the serialization should be written
     *                             {@url http://php.net/manual/en/book.stream.php}
     * @param $statements StatementIterator The StatementIterator containing all the Statements which should be
     *                                      serialized by the serializer.
     * @param $serialization string the serialization which should be used. If null N-Quads serialization will be used.
     * @return void
     */
    public function serializeIteratorToStream($outputStream, StatementIterator $statements, $serialization = null)
    {
        $handler = fopen($outputStream, 'a');
        foreach ($statements as $statement) {
            fwrite($handler, $statement->toNQuads() . PHP_EOL);
        }
        fclose($handler);
    }

    /**
     * @unstable
     * @return array of supported mime types which can be used by this serializer
     */
    public function getSupportedSerializations()
    {
        return ["application/n-triples", "application/n-quads"];
    }
}
