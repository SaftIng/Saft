<?php
namespace Saft\Backend\LocalStore\Store;

/**
 * Utility class to be used in a static manner, e. g.
 * <code>
 * Util::isValidUri('http://example.org')
 * </code>.
 */
final class Util
{
    // Static functions only
    private function __construct()
    {
    }

    /**
     * Checks if the given string is a valid URI.
     *
     * @param string $uri
     * @return boolean true if its a valid URI, false elsewhere
     */
    //TODO Unit test
    public static function isValidUri($uri)
    {
        // From https://www.ietf.org/rfc/rfc2396.txt (Appendix B)
        $URI_REGEX = '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/';
        return preg_match($URI_REGEX, $uri) === 1;
    }

    /**
     * Returns the absolute path of $relative to $root.
     * The returned path contains the OS' specific directory separator.
     *
     * @param string $root
     *            Base directory path
     * @param string $relative
     *            Relative directory path
     * @return string Absolute path from $relative relative to $root or
     *         false if it was not able to resolve
     */
    public static function getAbsolutePath($root, $relative)
    {
        // Get absolute path of $root
        $root = realpath($root);
        if ($root === false) {
            return false;
        }
        // Normalize $root, i. e. replace backslashes with slashes
        $root = str_replace('\\', '/', $root);
        // Last char of $root isn't a slash
        assert(substr($root, - 1) != '/');
        // Normalice $relative, i. e. replace backslashes with slashes
        $relative = str_replace('\\', '/', $relative);
        // Delete first slash in $relative if present
        if (substr($relative, 0, 1)) {
            $relative = substr($relative, 1);
        }
        // There is no slash at the end of $root and no slash at the
        // begin of $relative, so we can connect each with a slash
        $full = $root . '/' . $relative;
        // Because $relative can contain a symbolic links, like './../',
        // we have to resolve $full.
        $full = realpath($full);
        // Now $full can contain slashes or backslashes as path
        // separators.
        return $full;
    }

    /**
     * Unescape the given string. An escaping starts with a backslash followed
     * by one of the following escape chars 't', 'b', 'r', 'n', 'f', '"', '\''
     * or an unicode hex code starting by an 'u'.
     * @param string $str String to unescape
     * @throws \Exception when a syntax error has occured
     * @return string Unescaped string
     */
    public static function unescape($str)
    {
        // Skip, if there are no escape characters
        if (strpos($str, '\\') === false) {
            return $str;
        }

        $out = '';
        for ($i = 0, $n = strlen($str); $i < $n; $i++) {
            if ($str[$i] === '\\') {
                if ($i + 1 >= $n) {
                    throw new
                        SyntaxException('Unexpected end', SyntaxException::UNDEFINED, $i);
                }
                $i++;
                switch ($str[$i]) {
                    case 't':
                        $out .= "\t";
                        break;
                    case 'b':
                        $out .= "\b";
                        break;
                    case 'n':
                        $out .= "\n";
                        break;
                    case 'r':
                        $out .= "\r";
                        break;
                    case 'f':
                        $out .= "\f";
                        break;
                    case '"':
                        $out .= "\"";
                        break;
                    case '\'':
                        $out .= "\'";
                        break;
                    case 'u':
                        $i++;
                        $hex = '';
                        while (($i < $n) && self::isAlphaNumeric($str[$i])) {
                            $hex .= $str[$i];
                            $i++;
                        }
                        $i--;
                        $code = hexdec($hex);
                        if ($code < 0x80) {
                            $char = chr($code);
                        } elseif ($code < 0x800) {
                            $char = chr(($code >> 6) + 192)
                                . chr(($code & 63) + 128);
                        } elseif ($code < 0x10000) {
                            $char = chr(($code >> 12) + 224)
                                . chr((($code >> 6) & 63) + 128)
                                . chr(($code & 63) + 128);
                        } elseif ($code < 0x200000) {
                            $char = chr(($code >> 18) + 240)
                                . chr((($code >> 12) & 63) + 128)
                                . chr((($code >> 6) & 63) + 128)
                                . chr(($code & 63) + 128);
                        } else {
                            throw new SyntaxException(
                                'Invalid unicode',
                                SyntaxException::UNDEFINED,
                                $i - (1 + strlen($hex))
                            );
                        }
                        $out .= $char;
                        break;
                    default:
                        throw new SyntaxException(
                            'Invalid escape char',
                            SyntaxException::UNDEFINED,
                            $i - 1
                        );
                }
            } else {
                $out .= $str[$i];
            }
        }
        return $out;
    }
    
    public static function isAlphaNumeric($char)
    {
        return self::isDigit($char) || self::isAlpha($char);
    }

    public static function isDigit($char)
    {
        return '0' <= $char && $char <= '9';
    }

    public static function isAlpha($char)
    {
        return ('A' <= $char && $char <= 'Z')
            || ('a' <= $char && $char <= 'z');
    }
}
