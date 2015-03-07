<?php
namespace \Saft\Data;

interface ParserInterface
{
    /**
     * @note We have to decide how the ParserInterface should be implemented. One option
     * could be that a parser can accept multiple files/streams which are handled as one
     * graph and all statements are combined in the resulting StatementIterator.
     * So eather addFile or setFile should be used.
     * Maybe the PHP Stream API will be relevant here:
     * http://php.net/manual/en/book.stream.php
     * @unstable
     */
    public function addFile();
    /**
     * @unstable
     */
    public function setFile();

    /**
     * @return \Saft\Rdf\StatementIterator a StatementIterator containing all the Statements parsed by the parser to far
     */
    public function getStatementIterator();

    /**
     * @return array with a prefix mapping of the prefixes parsed so far
     */
    public function getCurrentPrefixlist();

}
