<?php
namespace Nyrados\Utils\File\Tests;

use PHPUnit\Framework\TestCase;
use Nyrados\Utils\File\File;

class FileTest extends TestCase
{
    public function getRootDirectory()
    {
        return __DIR__ . '/files';
    }

    public function getSourceDirectory(): string
    {
        return __DIR__ . '/files_source';
    }

    public function testSourceFolderExists(): void
    {
        $this->assertDirectoryExists($this->getSourceDirectory());
    }

    public function testCanReadDirectory()
    {
        $files = new File($this->getSourceDirectory());

        $scan = $files->withPath('folder')->scandir();

        $this->assertContains('element a', $scan);
        $this->assertContains('element b', $scan);
        $this->assertNotContains('element c', $scan);

        $this->assertContains('.', $scan);
        $this->assertContains('..', $scan);
    }

    public function testCanReadFile()
    {
        $file = new File($this->getSourceDirectory());
        $fp = $file->withPath('text.txt')->openFileStream();

        $this->assertIsResource($fp);
        $this->assertSame('Sample text', stream_get_contents($fp));

        fclose($fp);
    }

    public function testCanCreateDirectory()
    {
        $dir = new File($this->getSourceDirectory());
        $dir->assertExistance();

        $create = new File($this->getRootDirectory());
        $create->createDirIfNotExists();

        $this->assertFileExists($create->toString());
    }

    
    public function testCanReadDirectoryWithoutDots()
    {
        $root = (new File('/'))->scandir();
        $dir = (new File($this->getSourceDirectory()))->scandir(true);

        foreach ([$root, $dir] as $file) {
            $this->assertNotContains('.', $file);
            $this->assertNotContains('..', $file);
        }
    }

    public function testCanCopy()
    {
        $org = new File(__DIR__ . '/files_source');

        $org->withPath('work')->copy($this->getRootDirectory() . '/work');
        $org->withPath('folder')->copy($this->getRootDirectory() . '/folder');
        $org->withPath('text.txt')->copy($this->getRootDirectory() . '/text.txt');

        $this->assertDirectoryExists($this->getRootDirectory() . '/work');
        $this->assertFileExists($this->getRootDirectory() . '/work/essay.txt');
        $this->assertFileExists($this->getRootDirectory() . '/folder/element a');
    }

    public function testCanDeleteFile()
    {
        $file = new File($this->getRootDirectory() . '/work/backup.txt');

        $this->assertFileExists($file->toString());
        $file->delete();
        $this->assertFileDoesNotExist($file->toString());
    }

    public function testCanDeleteDirectory()
    {
        $dir = new File($this->getRootDirectory());
        $this->assertFileExists($dir->toString());
        $dir->delete();
        $this->assertFileDoesNotExist($dir->toString());
    }
}
