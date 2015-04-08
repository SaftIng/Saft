<?php
namespace Saft\Backend\LocalStore\Store;

use Saft\Rdf\Statement;
use Saft\Rdf\BlankNodeImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\StatementImpl;

final class NtriplesParser
{
    const STATEMENT_REGEX = '/^\s*(.+?)\s+(.+?)\s+(.+?)\s*\.\s*$/';
    const URI_REGEX = '/^<([^<>]+)>$/';
    const BLANK_NODE_REGEX = '/^_:([A-Za-z0-9]*)$/';
    const LITERAL_DATATYPE_REGEX = '/^"(.+)"\^\^<([^<>]+)>$/';
    const LITERAL_LANG_REGEX = '/^"(.+)"@([\w\-]+)$/';
    const LITERAL_REGEX = '/^"(.*)"$/';

    public static function parseStatment($line)
    {
        $matches=[];
        if (preg_match(self::STATEMENT_REGEX, $line, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $subject = self::parseSubject($matches[1][0]);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
            try {
                $predicate = self::parsePredicate($matches[2][0]);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[2][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
            try {
                $object = self::parseObject($matches[3][0]);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[3][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
            return new StatementImpl($subject, $predicate, $object);
        } else {
            throw new SyntaxException('Wrong statement format');
        }
    }

    public static function parseSubject($str)
    {
        $matches=[];
        if (preg_match(self::URI_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $uri = Util::unescape($matches[1][0]);
                return new NamedNodeImpl($uri);
            } catch (SyntaxException $e) {
                $column = $e->getColumn() + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
        } elseif (preg_match(self::BLANK_NODE_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            $id = Util::unescape($matches[1][0]);
            return new BlankNodeImpl($id);
        } else {
            throw new SyntaxException('Expected URI or blank node');
        }
    }

    public static function parsePredicate($str)
    {
        $matches=[];
        if (preg_match(self::URI_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $uri = Util::unescape($matches[1][0]);
                return new NamedNodeImpl($uri);
            } catch (SyntaxException $e) {
                $column = $e->getColumn() + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
        } else {
            throw new SyntaxException('Expected URI or blank node');
        }
    }

    public static function parseObject($str)
    {
        $matches=[];
        if (preg_match(self::LITERAL_DATATYPE_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $value = Util::unescape($matches[1][0]);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
            try {
                $datatype = Util::unescape($matches[2][0]);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[2][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
            // return new LiteralImpl($value, $datatype);
            // TODO Data Type
            throw new SyntaxException('Data Types currently not supported');
        } elseif (preg_match(self::LITERAL_LANG_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $value = Util::unescape($matches[1][0]);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
            $lang = $matches[2][0];
            return new LiteralImpl($value, $lang);
        } elseif (preg_match(self::LITERAL_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $value = Util::unescape($matches[1][0]);
                return new LiteralImpl($value);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
        } elseif (preg_match(self::URI_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            try {
                $uri = Util::unescape($matches[1][0]);
                return new NamedNodeImpl($uri);
            } catch (SyntaxException $e) {
                $column = ($e->isColumnDefined() ? $e->getColumn() : 0)
                    + $matches[1][1];
                throw new SyntaxException($e->getMessage(), SyntaxException::UNDEFINED, $column);
            }
        } elseif (preg_match(self::BLANK_NODE_REGEX, $str, $matches, PREG_OFFSET_CAPTURE)) {
            $id = Util::unescape($matches[1][0]);
            return new BlankNodeImpl($id);
        } else {
            throw new SyntaxException('Expected literal, URI or blank node');
        }
    }
}
