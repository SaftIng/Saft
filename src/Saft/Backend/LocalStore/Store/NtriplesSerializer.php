<?php
namespace Saft\Backend\LocalStore\Store;

use Saft\Rdf\Statement;
use Saft\Rdf\BlankNode;
use Saft\Rdf\Literal;
use Saft\Rdf\NamedNode;
use Saft\Rdf\Node;

class NtriplesSerializer
{
    public static function serializeStatement(Statement $statement)
    {
        if (!$statement->isConcrete()) {
            throw new \InvalidArgumentException('$statement is not concrete');
        }

        return sprintf(
            '%s %s %s .',
            self::serialize($statement->getSubject()),
            self::serialize($statement->getPredicate()),
            self::serialize($statement->getObject())
        );
    }

    public static function serialize(Node $node)
    {
        if (is_null($node)) {
            throw \Exception('$node is null');
        }

        if ($node instanceof BlankNode) {
            return self::serializeBlankNode($node);
        } elseif ($node instanceof Literal) {
            return self::serializeLiteral($node);
        } elseif ($node instanceof NamedNode) {
            return self::serializeNamedNode($node);
        } else {
            throw new \Exception('Expected BlankNode, Literal or NamedNode');
        }
    }

    public static function serializeBlankNode(BlankNode $node)
    {
        if (is_null($node)) {
            throw \Exception('$node is null');
        }

        return sprintf('_:%s', $node->getBlankId());
    }

    public static function serializeLiteral(Literal $node)
    {
        if (is_null($node)) {
            throw \Exception('$node is null');
        }

        return $node->toNQuads();
    }

    public static function serializeNamedNode(NamedNode $node)
    {
        if (is_null($node)) {
            throw \Exception('$node is null');
        }

        $uri = $node->getValue();
        return sprintf('<%s>', self::escape($uri));
    }

    private static function escape($str)
    {
        // Escapes <, > and space with there unicodes
        $str = str_replace(['<', '>', ' '], ['\u3c', '\u3e', '\u20'], $str);
        return $str;
    }
}
