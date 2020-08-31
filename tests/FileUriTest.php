<?php

namespace Nyrados\Utils\File\Tests;

use Nyrados\Utils\File\Path;
use Nyrados\Utils\File\FileUri;
use PHPUnit\Framework\TestCase;

class FileUriTest extends TestCase
{
    public function getPath($path)
    {
        return new FileUri($path);
    }

    /**
     * @dataProvider wrapperDataset
     */
    public function testCanNormalizeWrapper(string $before, string $wrapper, string $uri)
    {
        $w = new FileUri($before);

        $this->assertSame($wrapper, $w->getWrapper(), 'Failed to check that wrapper is correct');
        $this->assertSame($uri, $w->getUri(), 'Failed to check that uri is correct');
    }

    public function wrapperDataset()
    {
        return [
            // URI, Wrapper, normalized URI
            ['ftp://root:secret@host:123/abc/def/', 'ftp://root:secret@host:123/abc/def', 'ftp://root:secret@host:123/abc/def'],
            ['file:/C:/', 'C:/', 'file:/C:/'],
            ['file:/abc/def', '/abc/def', 'file:/abc/def'],
            ['abc://host/abc', 'abc://host/abc', 'abc://host/abc'],
            ['file://localhost/dir', 'file://localhost/dir', 'file://localhost/dir'],
            ['file://localhost/C:/', 'file://localhost/C:/', 'file://localhost/C:/'],
            ['file:///abc', '/abc', 'file:/abc'],
            ['abc:///path', 'abc:///path', 'abc:/path'],
            ['abc:///C:/path', 'abc:///C:/path', 'abc:/C:/path'],
            ['C:/Windows/', 'C:/Windows', 'file:/C:/Windows']
        ];
    }

    /**
     * @dataProvider uriStructureDataset
     */
    public function testCanDetectUriStructure(string $uri, string $scheme, string $authority, string $path)
    {
        $uri = new FileUri($uri);
        $this->assertSame($scheme, $uri->getScheme(), 'Failed to check that scheme is correct');
        $this->assertSame($authority, $uri->getAuthority(), 'Failed to check that authority is correct');
        $this->assertSame($path, $uri->getPath(), 'Failed to check that path is correct');
    }

    public function uriStructureDataset()
    {
        return [
            // URI, Scheme, Authority, Path
            ['ftp://root:secret@host:123/abc/def/', 'ftp', 'root:secret@host:123', '/abc/def'],
            ['sftp://root@host/C:/Users', 'sftp', 'root@host', 'C:/Users'],
            ['file:/C:/Users', 'file', '', 'C:/Users'],
            ['file://localhost/C:/Users', 'file', 'localhost', 'C:/Users'],
            ['file://localhost/home', 'file', 'localhost', '/home'],
            ['abc:/', 'abc', '', '/'],
            ['abc://root@localhost', 'abc', 'root@localhost', '/']
        ];
    }
}
