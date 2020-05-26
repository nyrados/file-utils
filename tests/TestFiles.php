<?php
namespace Nyrados\Utils\File\Tests;

use PHPUnit\Framework\TestCase;
use Nyrados\Utils\File\File;

abstract class TestFiles extends TestCase
{

    abstract public function getRootDirectory(): string;

    public function getSourceDirectory(): string
    {
        return __DIR__ . '/files_source';
    }

    public function testTestingEnvironmentCorrect(): void
    {
        $this->assertDirectoryExists($this->getSourceDirectory());
    }

    /**
     * @depends testCanCopyFiles
     *
     * @return void
     */
    public function testCanUseFilePath()
    {
        $file = new File($this->getRootDirectory());

        $this->assertTrue($file->isDir());
        $this->assertFalse($file->isFile());

        $this->assertSame('text.txt', $file->get('text.txt')->getName());
        $this->assertSame('test.txt', $file->get('folder/../test.txt')->getName());
    }

    public function testCanCreateDirectory()
    {
        $dir = new File($this->getSourceDirectory());
        $dir->assertExistance();

        $create = $dir->get($this->getRootDirectory());
        $create->createDirIfNotExists();

        $this->assertFileExists($create->toString());
    }

    /**
     * @depends testCanCreateDirectory
     *
     * @return void
     */
    public function testCanCopyFiles()
    {
        $org = new File(__DIR__ . '/files_source');

        $org->get('work')->copy($this->getRootDirectory() . '/work');
        $org->get('folder')->copy($this->getRootDirectory() . '/folder');
        $org->get('folder')->copy($this->getRootDirectory() . '/folder 2');
        $org->get('text.txt')->copy($this->getRootDirectory() . '/text.txt');

        $this->assertDirectoryExists($this->getRootDirectory(), '/work');
        $this->assertFileExists($this->getRootDirectory(), '/work/essay.txt');               
        $this->assertFileExists($this->getRootDirectory(), '/folder/element a'); 
        $this->assertDirectoryExists($this->getRootDirectory(), '/folder 2');
    }

    /**
     * @depends testCanCopyFiles
     * 
     * @return void
     */
    public function testCanReadDirectory()
    {
        $files = new File($this->getRootDirectory());

        $scan = $files->get('folder')->scandir();

        $this->assertContains('element a', $scan);
        $this->assertContains('element b', $scan);
        $this->assertNotContains('element c', $scan);

        $this->assertContains('.', $scan);
        $this->assertContains('..', $scan);
    }

    public function testCanDeleteFile()
    {
        $file = new File($this->getRootDirectory() . '/work/backup.txt');
        
        $this->assertFileExists($file->toString());
        $file->delete();
        $this->assertFileDoesNotExist($file->toString());
    }

    public function testCanRename()
    {
        $file = new File($this->getRootDirectory() . '/folder 2');
        $this->assertFileExists($file->toString());

        $file->rename('work/../work/sources');
        $this->assertFileExists($this->getRootDirectory() . '/work/sources/');
        $this->assertFileDoesNotExist($this->getRootDirectory() . '/folder 2');
    }


    /**
     * @depends testCanReadDirectory
     *
     * @return void
     */
    public function testCanReadDirectoryWithoutDots()
    {
        $root = (new File('/'))->scandir();
        $dir = (new File($this->getRootDirectory() . '/folder'))->scandir(true);

        foreach ([$root, $dir] as $file) {
            $this->assertNotContains('.', $file);
            $this->assertNotContains('..', $file);
        }
    }

    /**
     * @depends testCanCopyFiles
     *
     * @return void
     */
    public function testCanOpenResource()
    {
        $resource = (new File($this->getRootDirectory() . '/text.txt'))->openFileStream();
        $this->assertIsResource($resource);
    }


    /**
     * @depends testCanCreateDirectory
     *
     * @return void
     */
    public function testCanDeleteDirectory()
    {
        $dir = new File($this->getRootDirectory() . '');
        $this->assertFileExists($dir->toString());
        $dir->delete();
        $this->assertFileDoesNotExist($dir->toString());
    }
}