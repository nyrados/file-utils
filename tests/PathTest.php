<?php

namespace Nyrados\Utils\File\Tests;

use GuzzleHttp\Psr7\Uri;
use Nyrados\Utils\File\FileUri;
use Nyrados\Utils\File\FileUtils;
use Nyrados\Utils\File\Path;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class PathTest extends TestCase
{
    /**
     * @dataProvider normalizeDataSet
     */
    public function testCanNormalize($before, $expect): void
    {
        $this->assertSame($expect, (new Path($before))->toString());
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
            ['E:\\abc\\.\\def\\ ', 'E:/abc/def'],
            ['E:', 'E:/'],
            ['F://', 'F:/'],
            ['C:\\path\\to\\', 'C:/path/to'],
            ['', ''],
            ['abc', 'abc'],
            ['abc/', 'abc'],
            [' X:\\abc\\.\\..\\.file\\image.jpg', 'X:/abc/../.file/image.jpg']
        ];
    }


    public function testHasCorrectBehaviour(): void
    {
        $path = new Path('/path/to/my/dir');
        $expect = '/path/to/my/dir';

        $this->assertSame($expect, (string) $path);
        $this->assertSame($expect, $path->toString());
        $this->assertSame($expect, $path->getPath());
        $this->assertSame($expect, (new Path($path))->getPath());

        $this->assertSame('/path/to/my/image.png', $path->getParent()->withPath('image.png')->toString());
        $this->assertSame('/image.png', $path->withPath('/image.png')->toString());
    }


    /**
     * @dataProvider normalizeBackwardsDataset
     * @depends testCanNormalize
     */
    public function testCanNormalizeBackwardsPath($before, $expect)
    {
        $this->assertSame($expect, (new Path($before))->withoutBackwards()->toString());
    }

    public function normalizeBackwardsDataset()
    {
        //Before => expected
        return [
            ['C:/..', 'C:/'],
            ['C:/abc/def/../', 'C:/abc'],
            ['/abc/def/../../../', '/'],
            ['/abc/../', '/'],
            ['/abc/def/..', '/abc'],
            ['/abc/./def/../abc', '/abc/abc'],
            ['./abc/../', ''],
            ['abc/../', ''],
            ['abc/def/..', 'abc']
        ];
    }


    /**
     * @dataProvider absolutePathDataset
     * @depends testCanNormalize
     */
    public function testCanDetectAbsolutePath($path, $expect)
    {
        $path = new Path($path);

        if ($expect) {
            $this->assertTrue($path->isAbsolute());
            $this->assertFalse($path->isRelative());

            $this->assertTrue($path->asRelative()->isRelative());

        } else {
            
            $this->assertTrue($path->isRelative());
            $this->assertFalse($path->isAbsolute());

            $this->assertTrue($path->asAbsolute()->isAbsolute());
            $this->assertTrue($path->asAbsolute('C:/abc')->isAbsolute());



        }
    }
    
    public function absolutePathDataset()
    {
        return [

            //Path => isAbsolute

            ['/abc', true],
            ['E:', true],
            ['F://', true],

            ['C:\\path\\to\\', true],
            ['', false],
            ['abc', false],
            ['abc/', false]
        ];
    }


    /**
     * @dataProvider windowsPathDataset
     * @depends testCanNormalize
     */    
    public function testCanDetectWindowsPath($path, $expect)
    {
        $value = (new Path($path))->isWindowsPath();
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