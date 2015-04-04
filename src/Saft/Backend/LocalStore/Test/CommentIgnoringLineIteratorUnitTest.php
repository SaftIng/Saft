<?php
namespace Saft\Backend\LocalStore\Test;

use Saft\Backend\LocalStore\Store\CommentIgnoringLineIterator;

class CommentIgnoringLineIteratorUnitTest extends \PHPUnit_Framework_TestCase
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
        new CommentIgnoringLineIterator(null);
    }

    const NUM_REWINDS = 3;

    const NO_CONTENT = '';

    public function testNoContent()
    {
        $filename = $this->randomFileName();
        $this->writeFile($filename, self::NO_CONTENT);

        $it = new CommentIgnoringLineIterator($filename);
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

        $it = new CommentIgnoringLineIterator($filename);
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

        $it = new CommentIgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $it->next();
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
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

        $it = new CommentIgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $it->next();
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
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

        $it = new CommentIgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $it->next();
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
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

        $it = new CommentIgnoringLineIterator($filename);
        for ($i = 1; $i <= self::NUM_REWINDS; $i++) {
            $it->rewind();
            $this->assertTrue($it->valid());
            $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
            $it->next();
            $this->assertEquals('consetetur sadipscing elitr,', $it->current());
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
    
        $it = new CommentIgnoringLineIterator($filename);
        $it->rewind();
        $this->assertTrue($it->valid());
        $this->assertEquals('Lorem ipsum dolor sit amet,', $it->current());
        $it->next();
        $this->assertEquals('consetetur sadipscing elitr,', $it->current());
        $it->next();
        $this->assertFalse($it->valid());
        $it->close();
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
