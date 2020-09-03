<?php

namespace Nyrados\Utils\File\Tests;

use PHPUnit\Framework\TestCase;
use Nyrados\Utils\File\File;

abstract class AbstractFileTest extends TestCase
{
    abstract public function getSourceDirectory(): string;

    abstract public function getPlaygroundDirectory(): string;

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
    
    public function testCanReadDirectoryWithoutDots()
    {
        $root = (new File('/'))->scandir();
        $dir = (new File($this->getSourceDirectory()))->scandir(true);

        foreach ([$root, $dir] as $file) {
            $this->assertNotContains('.', $file);
            $this->assertNotContains('..', $file);
        }
    }

    public function testCanCreateDirectory()
    {
        $dir = new File($this->getSourceDirectory());
        $dir->assertExistance();

        $create = new File($this->getPlaygroundDirectory());
        $create->createDirIfNotExists();

        $this->assertFileExists($create->toString());
    }

    public function testCanCopy()
    {
        $org = new File(__DIR__ . '/files_source');

        $org->withPath('work')->copy($this->getPlaygroundDirectory() . '/work');
        $org->withPath('folder')->copy($this->getPlaygroundDirectory() . '/folder');
        $org->withPath('text.txt')->copy($this->getPlaygroundDirectory() . '/text.txt');

        $this->assertDirectoryExists($this->getPlaygroundDirectory() . '/work');
        $this->assertFileExists($this->getPlaygroundDirectory() . '/work/essay.txt');
        $this->assertFileExists($this->getPlaygroundDirectory() . '/folder/element a');
    }

    public function testCanDeleteFile()
    {
        $file = new File($this->getPlaygroundDirectory() . '/work/backup.txt');

        $this->assertFileExists($file->toString());
        $file->delete();
        $this->assertFileDoesNotExist($file->toString());
    }

    public function testCanDeleteDirectory()
    {
        $dir = new File($this->getPlaygroundDirectory());
        $this->assertFileExists($dir->toString());
        $dir->delete();
        $this->assertFileDoesNotExist($dir->toString());
    }
}
