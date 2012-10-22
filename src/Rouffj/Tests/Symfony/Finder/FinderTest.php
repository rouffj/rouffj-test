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
