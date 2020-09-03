<?php
namespace Nyrados\Utils\File\Tests;

use PHPUnit\Framework\TestCase;
use Nyrados\Utils\File\File;

class SimpleFileTest extends AbstractFileTest
{
    public function getPlaygroundDirectory(): string
    {
        return __DIR__ . '/files';
    }

    public function getSourceDirectory(): string
    {
        return __DIR__ . '/files_source';
    }
}
