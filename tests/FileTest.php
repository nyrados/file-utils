<?php

use Nyrados\Utils\File\File;
use Nyrados\Utils\File\Tests\PathTest;
use Nyrados\Utils\File\Tests\TestFiles;
use PHPUnit\Framework\TestCase;

class FileTest extends TestFiles
{
    public function getRootDirectory(): string
    {
        return __DIR__ . '/files';
    }
}