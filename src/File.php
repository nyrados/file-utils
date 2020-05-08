<?php
namespace Nyrados\Utils\File;

use Psr\Http\Message\UriInterface;

class File extends Target
{
    public function __construct(UriInterface $uri)
    {
        parent::__construct($uri);
        $this->assertIsDir();
    }
}