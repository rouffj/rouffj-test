<?php

namespace Rouffj\Tests\Symfony\Finder;

use Rouffj\Tests\TestCase;
use Symfony\Component\Finder\Finder;

class FinderTest extends TestCase
{
    private $fixtures;

    public function doSetUp()
    {
        $this->fixtures = __DIR__.'/Fixtures';
    }

    public function testHowToListSpecificFilesInDifferentDirectories()
    {
        $files = array('Dir1/file1-1.php', 'file1.php');

        // 1st solution, do exactly the job.
        $iterator = Finder::create()
            ->files()
            ->in($this->fixtures)
            ->filter(function (\SplFileInfo $file) use ($files) {
                // filter() closure should return false to remove a file from result
                return in_array($file->getRelativePathname(), $files);
            });
        $this->assertEquals(2, count($iterator), 'search all files with a relative pathname matching the $files entries');

        // 2nd solution, do the job but is not perfect because if 2 files have the names they will be return either.
        $iterator = Finder::create()
            ->files()
            ->name($files[0])
            ->name($files[1])
            ->in($this->fixtures);
        $this->assertCount(2, $iterator, 'search recursively all files named file1-1.php and file1.php');
    }

    public function testHowToListPhpFilesInSubdirectories()
    {
        $finder = new Finder();

        $iterator = $finder
            ->files()
            ->name('*.php')
            ->in($this->fixtures);

        $this->assertEquals('Symfony\Component\Finder\Finder', get_class($iterator));
        $this->assertCount(3, $iterator);

        $files = iterator_to_array($iterator);
        $this->assertEquals(true, array_key_exists($this->fixtures.'/file1.php', $files));
        $this->assertEquals(true, array_key_exists($this->fixtures.'/Dir1/Dir1-1/file1-1-1.php', $files));
    }

    public function testHowToManipulateFileFoundWithFinder()
    {
        $finder = new Finder();
        $iterator = $finder
            ->files()
            ->name('file1-1.php')
            ->in($this->fixtures);

        $files = iterator_to_array($iterator);

        $file = $files[$this->fixtures.'/Dir1/file1-1.php'];

        $this->assertEquals('Symfony\Component\Finder\SplFileInfo', get_class($file));
        $this->assertEquals("<?php echo 'hello world !!';\n", $file->getContents());
        $this->assertEquals('Dir1/file1-1.php', $file->getRelativePathname(), 'usefull for Twig');

        // feature extended from \SplFileInfo
        $this->assertEquals($this->fixtures.'/Dir1/file1-1.php', $file->getPathname());
        $this->assertEquals('file1-1.php', $file->getFilename());
    }
}
