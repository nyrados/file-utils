<?php

namespace Nyrados\Utils\File\Tests;

use Nyrados\Utils\File\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function getPath($path)
    {
        return new Path($path);
    }

    /**
     * @dataProvider normalizeDataSet
     */
    public function testCanNormalize($before, $expect): void
    {
        $this->assertSame($expect, $this->getPath($before)->getPath());
    }

    public function normalizeDataSet()
    {
        return [

            //Before => Expect

            ['.', ''],
            ['..', '..'],
            ['/abc/./a', '/abc/a'],
            ['./abc', 'abc'],
            ['../abc', '../abc'],
            ['/../', '/..'],
            ['/abc/../', '/abc/..'],
            ['/C:/abc', 'C:/abc'],
            ['/C:', 'C:/'],
            ['  abc/   ', 'abc'],
            ['/abc/.', '/abc'],
            ['C:\\.', 'C:/'],
            ['/abc', '/abc'],
            ['/abc/', '/abc'],
            ['  / ', '/'],
            ['/abc', '/abc'],
            ['E:\\abc\\.\\def\\ ', 'E:/abc/def'],
            ['E:', 'E:/'],
            ['F://', 'F:/'],
            ['C:\\path\\to\\', 'C:/path/to'],
            ['', ''],
            ['abc', 'abc'],
            ['abc/', 'abc'],
            [' X:\\abc\\.\\..\\.dir\\image.jpg', 'X:/abc/../.dir/image.jpg']
        ];
    }

    public function testHasCorrectStringifyBehaviour(): void
    {
        $path = new Path('/path/to/my/dir');
        $expect = '/path/to/my/dir';

        $this->assertSame($expect, (string) $path);
        $this->assertSame($expect, $path->toString());
        $this->assertSame($expect, $path->getPath());
    }

    public function testCanUseWithPath(): void
    {
        $path = new Path('/path/');

        $this->assertSame('/dir/image.png', $path->withPath('/dir/image.png')->getPath());
        $this->assertSame('/path/dir/image.png', $path->withPath('dir/image.png')->getPath());

        $path = new Path('/');
        $this->assertSame('/abc', $path->withPath('abc')->getPath());
        $this->assertSame('/abc', $path->withPath('/abc')->getPath());
    }


    /**
     * @dataProvider absolutePathDataset
     * @depends testCanNormalize
     */
    public function testCanDetectPathType($path, $expectAbsolute, $expectRoot)
    {
        $path = $this->getPath($path);

        if ($expectAbsolute) {
            $this->assertTrue($path->isAbsolute());
            $this->assertFalse($path->isRelative());

            $this->assertTrue($path->asRelative()->isRelative());
        } else {
            $this->assertTrue($path->isRelative());
            $this->assertFalse($path->isAbsolute());

            $this->assertTrue($path->asAbsolute()->isAbsolute());
            $this->assertTrue($path->asAbsolute('C:/abc')->isAbsolute());
        }

        $expectRoot ? $this->assertTrue($expectRoot) : $this->assertFalse($expectRoot);
    }

    public function absolutePathDataset()
    {
        return [

            //Path => isAbsolute, isRoot

            ['/abc', true, false],
            ['E:', true, true],
            ['F://', true, true],
            ['/', true, true],

            ['C:\\path\\to\\', true, false],
            ['./', false, false],
            ['', false, false],
            ['abc', false, false],
            ['abc/', false, false],
            
        ];
    }


    /**
     * @dataProvider windowsPathDataset
     * @depends testCanNormalize
     */
    public function testCanDetectWindowsPath($path, $expect): void
    {
        $value = $this->getPath($path)->isWindowsPath();
        $expect ? $this->assertTrue($value) : $this->assertFalse($value);
    }

    public function windowsPathDataset()
    {
        return [

            //Path => isWindowsPath

            ['C:/', true],
            ['E:', true],
            ['C:\\Windows\\', true],
            ['XX:/', false],
            ['F', false],
            ['abc', false],
            ['/abc', false]
        ];
    }
}
