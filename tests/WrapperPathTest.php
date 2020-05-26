<?php

namespace Nyrados\Utils\File\Tests;

use Nyrados\Utils\File\WrapperPath;
use PHPUnit\Framework\TestCase;

class WrapperPathTest extends TestCase
{

    /**
     * @dataProvider wrapperDataset
     */
    public function testCanBuildWrapper($before, $wrapper, $uri, $path)
    {
        $w = new WrapperPath($before);

        $this->assertSame($wrapper, $w->getWrapper());
        $this->assertSame($uri, $w->getUri());
        $this->assertSame($path, $w->getPath());
    }

    public function wrapperDataset()
    {
        return [
            // Before, Wrapper, Uri, Path
            ['ftp://root:secret@host:123/abc/def/', 'ftp://root:secret@host:123/abc/def', 'ftp://root:secret@host:123/abc/def', '/abc/def'],
            ['file:/C:/', 'file://C:/', 'file://C:/', 'C:/'],
            ['file:/abc/def', 'file:///abc/def', 'file:///abc/def', '/abc/def'],
            ['abc://host/abc', 'abc://host/abc', 'abc://host/abc', '/abc'],
            ['file:///abc', 'file:///abc', 'file:///abc', '/abc'],
            ['abc:///path', 'abc:///path', 'abc:/path', '/path']
        ];
    }
}