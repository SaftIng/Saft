<?php

use pietercolpaert\hardf\Util;
use Saft\Rdf\NodeFactory;
use Saft\Rdf\Statement;
use Saft\Rdf\StatementFactory;

/**
 * @param array $triple
 * @param NodeFactory $nodeFactory
 * @param StatementFactory $statementFactory
 *
 * @return Statement
 *
 * @throws \Exception if errors during transformation
 */
function saftAdditionHardfTripleToStatement(
    array $triple,
    NodeFactory $nodeFactory,
    StatementFactory $statementFactory
): Statement {
    /*
     * handle subject
     */
    $subject = null;
    if (Util::isIRI($triple['subject'])) {
        $subject = $nodeFactory->createNamedNode($triple['subject']);
    } elseif (Util::isBlank($triple['subject'])) {
        $subject = $nodeFactory->createBlankNode(\substr($triple['subject'], 2));
    } else {
        throw new \Exception('Invalid node type for subject found: '.$triple['subject']);
    }

    /*
     * handle predicate
     */
    $predicate = null;
    if (Util::isIRI($triple['predicate'])) {
        $predicate = $nodeFactory->createNamedNode($triple['predicate']);
    } else {
        throw new \Exception('Invalid node type for predicate found: '.$triple['predicate']);
    }

    /*
     * handle object
     */
    $object = null;
    if (Util::isIRI($triple['object'])) {
        $object = $nodeFactory->createNamedNode($triple['object']);
    } elseif (Util::isBlank($triple['object'])) {
        $object = $nodeFactory->createBlankNode(\substr($triple['object'], 2));
    } elseif (Util::isLiteral($triple['object'])) {
        // safety check, to avoid fatal error about missing Error class in hardf
        // FYI: https://github.com/pietercolpaert/hardf/pull/12
        // TODO: remove this here, if fixed
        $int = \preg_match('/"(\n+\s*.*\n+\s*)"/si', $triple['object'], $match);
        if (0 < $int) {
            $value = $match[1];
            $lang = null;
            $datatype = null;

        /*
         * normal case
         */
        } else {
            // get value
            \preg_match('/"(.*)"/si', $triple['object'], $match);
            $value = $match[1];

            $lang = Util::getLiteralLanguage($triple['object']);
            $lang = '' == $lang ? null : $lang;
            $datatype = Util::getLiteralType($triple['object']);
        }

        $object = $nodeFactory->createLiteral($value, $datatype, $lang);
    } else {
        throw new \Exception('Invalid node type for object found: '.$triple['object']);
    }

    /*
     * handle graph, if available
     */
    $graph = null;
    if (Util::isIRI($triple['graph'])) {
        $graph = $nodeFactory->createNamedNode($triple['graph']);
    }

    return $statementFactory->createStatement($subject, $predicate, $object, $graph);
}
