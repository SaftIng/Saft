<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\IgnoringLineIterator;

class IgnoringLineIteratorUnitTest extends \PHPUnit_Framework_TestCase
{
    // Used for temporary text files
    private $tempDirectory = null;

    public function setUp()
    {
        $this->tempDirectory = TestUtil::createTempDirectory();
    }

    public function tearDown()
    {
        if (!is_null($this->tempDirectory)) {
            TestUtil::deleteDirectory($this->tempDirectory);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorChecksForNull()
    {
        new IgnoringLineIterator(null);
    }

    const NUM_REWINDS = 3;

    const NO_CONTENT = '';

    public function testNoContent()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::NO_CONTENT);

        $it = new IgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertFalse($it->valid());
        }
        $it->close();
    }

    const ONLY_ONE_COMMENT = <<<EOD
#Some comment
EOD;

    public function testOnlyOneComment()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_ONE_COMMENT);

        $it = new IgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertFalse($it->valid());
        }
        $it->close();
    }

    const ONLY_CONTENT = <<<EOD
Lorem ipsum dolor sit amet,
consetetur sadipscing elitr,
EOD;

    public function testOnlyContent()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $this->assertEquals(0, $it->key());
            $it->next();
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
            $this->assertEquals(1, $it->key());
            $it->next();
            $this->assertFalse($it->valid());
        }
        $it->close();
    }

    const STARTING_COMMENT = <<<EOD
#Prolog
Lorem ipsum dolor sit amet,
#Some comment
consetetur sadipscing elitr,
EOD;

    public function testStartingComment()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::STARTING_COMMENT);

        $it = new IgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $this->assertEquals(1, $it->key());
            $it->next();
            $this->assertTrue($it->valid());
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
            $this->assertEquals(3, $it->key());
            $it->next();
            $this->assertFalse($it->valid());
        }
        $it->close();
    }

    const ENDING_COMMENT = <<<EOD
Lorem ipsum dolor sit amet,
#Some comment
consetetur sadipscing elitr,
#Epilog
EOD;

    public function testEndingComment()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ENDING_COMMENT);

        $it = new IgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $this->assertEquals(0, $it->key());
            $it->next();
            $this->assertTrue($it->valid());
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
            $this->assertEquals(2, $it->key());
            $it->next();
            $this->assertFalse($it->valid());
        }
        $it->close();
    }

    const STARTING_AND_ENDING_COMMENT = <<<EOD
#Prolog
Lorem ipsum dolor sit amet,
#Some comment
consetetur sadipscing elitr,
#Epilog
EOD;

    public function testStartingAndEndingComment()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::STARTING_AND_ENDING_COMMENT);

        $it = new IgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $this->assertEquals(1, $it->key());
            $it->next();
            $this->assertTrue($it->valid());
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
            $this->assertEquals(3, $it->key());
            $it->next();
            $this->assertFalse($it->valid());
        }
        $it->close();
    }

    const IGNORE = <<<EOD
Lorem ipsum dolor sit amet,
# Some comment
  # A comment with starting space
# Followed by a blank line

# Followed by a blank line with starting space
  
consetetur sadipscing elitr,
EOD;

    public function testLineIgnoring()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::IGNORE);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $this->assertTrue($it->valid());
        $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
        $this->assertEquals(0, $it->key());
        $it->next();
        $this->assertTrue($it->valid());
        $this->assertEquals('consetetur sadipscing elitr,', $it->current());
        $this->assertEquals(7, $it->key());
        $it->next();
        $this->assertFalse($it->valid());
        $it->close();
    }

    public function testIsClosed()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $this->assertFalse($it->isClosed());
        $it->close();
        $this->assertTrue($it->isClosed());
    }

    public function testClose()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $it->close();
        $this->assertTrue($it->isClosed());
    }

    /**
     * @expectedException \LogicException
     */
    public function testCurrentChecksIfClosed()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->current();
    }

    /**
     * @expectedException \Exception
     */
    public function testCurrentChecksIfValid()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        // Read to the end
        while ($it->valid()) {
            $it->next();
        }
        assert(!$it->valid());
        // No such element
        $it->current();
    }

    /**
     * @expectedException \LogicException
     */
    public function testKeyChecksIfClosed()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->key();
    }

    /**
     * @expectedException \Exception
     */
    public function testKeyChecksIfValid()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        // Read to the end
        while ($it->valid()) {
            $it->next();
        }
        assert(!$it->valid());
        // No such element
        $it->key();
    }

    /**
     * @expectedException \LogicException
     */
    public function testNextChecksIfClosed()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->next();
    }

    /**
     * @expectedException \Exception
     */
    public function testNextChecksIfValid()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        // Read to the end
        while ($it->valid()) {
            $it->next();
        }
        assert(!$it->valid());
        // No such element
        $it->next();
    }

    /**
     * @expectedException \LogicException
     */
    public function testRewindChecksIfClosed()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->rewind();
    }

    /**
     * @expectedException \LogicException
     */
    public function testValidChecksIfClosed()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::ONLY_CONTENT);

        $it = new IgnoringLineIterator($filename);
        $it->rewind();
        $it->close();
        // Will fail while closed
        $it->valid();
    }

    private function randomFileName()
    {
        return $this->tempDirectory . DIRECTORY_SEPARATOR . uniqid() . '.txt';
    }

    private static function writeFile($filename, $content)
    {
        $file = fopen($filename, 'w');
        if ($file === false) {
            throw new \Exception('Unable to write file ' . $filename);
        }
        fwrite($file, $content);
        fclose($file);
    }
}
