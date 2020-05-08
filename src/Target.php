<?php
namespace Nyrados\Utils\File;

use Nyrados\Utils\File\Exception\FileAlreadyExistsException;
use Nyrados\Utils\File\Exception\FileException;
use Nyrados\Utils\File\Exception\FileNotFoundException;
use Nyrados\Utils\File\Exception\FileNotReadableException;
use Nyrados\Utils\File\Exception\FileNotWriteableException;
use Nyrados\Utils\File\Exception\FileTypeException;
use Psr\Http\Message\UriInterface;

class Target
{

    /** @var UriInterface */
    protected $uri;

    public function __construct(UriInterface $uri)
    {
        $this->uri = $uri->withPath(Normalizer::normalize($uri->getPath(Normalizer::NORMALIZE_NO_END_SLASH)));
    }

    /** RECIEVE METHODS */

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return $this->getUri()->getPath();
    }

    public function exists(): bool
    {
        return file_exists($this->uri);
    }

    public function notExists(): bool
    {
        return !$this->exists();
    }

    public function isDir()
    {
        return is_dir($this->toString());
    }

    public function isFile()
    {
        return is_file($this->toString());
    }

    public function isReadable()
    {
        return is_readable($this->toString());
    }

    public function isWriteable()
    {
        return is_writable($this->toString());
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return (string) $this->uri;
    }

    public function specify(): self
    {
        $this->assertExistance();

        if($this->isDir()) {
            return new Directory($this->uri);
        }


    }

    /** ASSERT METHODS */

    protected function assert(callable $callback, FileException $e = null)
    {
        error_clear_last();
        if(!@$callback()) {

            $e = $e instanceof FileException ? $e : new FileException($this);

            if(error_get_last() != null) {
                $e->setCause(error_get_last()['message']);
            }
            
            throw $e;
        }
    }

    public function assertExistance(string $message = '')
    {
        $this->assert([$this, 'exists'], new FileNotFoundException($this, $message));
    }

    public function assertNotExistance(string $message = '')
    {
        $this->assert([$this, 'notExists'], new FileAlreadyExistsException($this, $message));
    }

    public function assertIsDir(string $message = '')
    {
        $this->assertExistance();
        $this->assert([$this, 'isDir'], new FileTypeException($this, $message));     
    }

    public function assertIsFile(string $message = '')
    {
        $this->assertExistance();
        $this->assert([$this, 'isFile'], new FileTypeException($this, $message));            
    }

    public function assertReadable(string $message = '')
    {
        $this->assertExistance();
        $this->assert([$this, 'isReadable'], new FileNotReadableException($this, $message));
    }

    public function assertWriteable(string $message = '')
    {
        $this->assertExistance();
        $this->assert([$this, 'isWriteable'], new FileNotWriteableException($this, $message));
    }


}