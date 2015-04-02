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
}
