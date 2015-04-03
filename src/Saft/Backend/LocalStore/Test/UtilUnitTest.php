<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\Util;
use Saft\Backend\LocalStore\Store\SyntaxException;

class UtilUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testUnescapeOfNonEscapeChar()
    {
        try {
            Util::unescape('01234\\x');
            $this->fail();
        } catch (SyntaxException $e) {
            $this->assertFalse($e->isRowDefined());
            $this->assertEquals(5, $e->getColum());
        }
    }

    public function testUnescapeWithBackshlashAtTheEnd()
    {
        try {
            Util::unescape('01234\\');
            $this->fail();
        } catch (SyntaxException $e) {
            $this->assertFalse($e->isRowDefined());
            $this->assertEquals(5, $e->getColum());
        }
    }

    public function testUnescape()
    {
        $this->assertEquals('Lorem Ipsum', Util::unescape('Lorem Ipsum'));
        $this->assertEquals("Lorem\tIpsum", Util::unescape('Lorem\tIpsum'));
        $this->assertEquals("Lorem\bIpsum", Util::unescape('Lorem\bIpsum'));
        $this->assertEquals("Lorem\rIpsum", Util::unescape('Lorem\rIpsum'));
        $this->assertEquals("Lorem\nIpsum", Util::unescape('Lorem\nIpsum'));
        $this->assertEquals("Lorem\fIpsum", Util::unescape('Lorem\fIpsum'));
        $this->assertEquals("Lorem\'Ipsum", Util::unescape('Lorem\\\'Ipsum'));
        $this->assertEquals("Lorem\"Ipsum", Util::unescape('Lorem\\"Ipsum'));
        $this->assertEquals("ABC", Util::unescape('\\u41\\u42\\u43'));
    }

    public function testIsDigit()
    {
        $digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        foreach ($digits as $char) {
            $this->assertTrue(Util::isDigit($char));
        }

        $nonDigits = ['A', 'a', ' ', "\t", "\n", ' ', '.'];
        foreach ($nonDigits as $char) {
            $this->assertFalse(Util::isDigit($char));
        }
    }

    public function testIsAlpha()
    {
        $alphas = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
        ];
        foreach ($alphas as $char) {
            $this->assertTrue(Util::isAlpha($char));
        }

        $nonAlphas = ['0', '1', '2', '9', ' ', "\t", "\n", ' ', '.'];
        foreach ($nonAlphas as $char) {
            $this->assertFalse(Util::isAlpha($char));
        }
    }

    public function testIsAlphaNumeric()
    {
        $alphaNums = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];
        foreach ($alphaNums as $char) {
            $this->assertTrue(Util::isAlphaNumeric($char));
        }

        $nonAlphaNums = [' ', "\t", "\n", ' ', '.'];
        foreach ($nonAlphaNums as $char) {
            $this->assertFalse(Util::isAlpha($char));
        }
    }
}
