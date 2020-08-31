<?php

namespace Nyrados\Utils\File;

use Directory;
use Nyrados\Utils\File\Exception\FileAlreadyExistsException;
use Nyrados\Utils\File\Exception\FileException;
use Nyrados\Utils\File\Exception\FileNotFoundException;
use Nyrados\Utils\File\Exception\FileNotReadableException;
use Nyrados\Utils\File\Exception\FileNotWriteableException;
use Nyrados\Utils\File\Exception\FileTypeException;

/**
 * File Class for a controlled File Access
 */
final class File extends FileUri
{
    /**
     * Checks if file exists.
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return file_exists($this->toString());
    }

    /**
     * Checks if file does not exists.
     *
     * @return boolean
     */
    public function notExists(): bool
    {
        return !$this->exists();
    }

    /**
     * Checks if file is a directory.
     *
     * @return boolean
     */
    public function isDir()
    {
        return is_dir($this->toString());
    }

    /**
     * Checks if file is a file.
     *
     * @return boolean
     */
    public function isFile()
    {
        return is_file($this->toString());
    }

    /**
     * Checks File is readable.
     *
     * @return boolean
     */
    public function isReadable()
    {
        return is_readable($this->toString());
    }

    /**
     * Checks if File is writeable.
     *
     * @return boolean
     */
    public function isWriteable()
    {
        return is_writable($this->toString());
    }

    /**
     * Gives information about the file.
     *
     * @see https://www.php.net/manual/en/function.stat.php
     *
     * @return void
     */
    public function stat(): array
    {
        $this->assertExistance();
        return stat($this->toString());
    }

    /** CREATE METHODS */

    /**
     * Creates an empty directory if the current does not exists.
     *
     * @param integer $mode
     * @param boolean $recrusive
     * @return void
     */
    public function createDirIfNotExists($mode = 0777, $recrusive = true): void
    {
        if ($this->notExists()) {
            $this->assert(function () use ($mode, $recrusive) {
                mkdir($this->toString(), $mode, $recrusive);
            }, 'Failed to create Directory');
            $this->assertIsDirectory();
        }
    }

    /**
     * Creates an empty file if the current does not exists.
     *
     * @return void
     */
    public function createFileIfNotExitst()
    {
        if ($this->notExists()) {
            $this->openFileStream('w');
            $this->assertIsFile();
        }
    }

    /**
     * Opens Resource if file is a directory
     *
     * @param boolean $ignoreDots
     * @return Directory
     */
    public function openDirectory(): Directory
    {
        $this->assertIsDirectory('Failed to open directory');
        return dir($this->toString());
    }

    /**
     * Opens Resource if file is a file.
     *
     * @see https://www.php.net/manual/en/function.fopen
     *
     * @throws FileException
     * @param string $mode
     * @return resource
     */
    public function openFileStream(string $mode = 'r')
    {
        return $this->assert(function () use ($mode) {
            return fopen($this->toString(), $mode);
        });
    }

    /**
     * Renames/moves the current file to $destination
     *
     * @param string $destination
     * @return void
     */
    public function rename(string $destination)
    {
        $destination = new self($destination);
        $this->assert(function () use ($destination) {
            rename($this->toString(), $destination->toString());
        }, 'Failed to rename');
    }

    /**
     * Lists Names of Children if file is a directory.
     *
     * @throws FileException
     * @param boolean $ignoreDots if true ., .. will be removed from the return array
     * @return string[]
     */
    public function scandir($ignoreDots = false): array
    {
        $this->assertIsDirectory();
        $dir = $this->openDirectory();
        $rs = [];
        while (false !== ($file = $dir->read())) {
            if ($ignoreDots && ($file === '..' || $file === '.')) {
                continue;
            }

            $rs[] = $file;
        }

        sort($rs);

        $dir->close();

        return $rs;
    }

    /**
     * Returns Children if File is a Directory.
     *
     * @throws FileException
     * @return static[]
     */
    public function getChildren(): array
    {
        $result = [];
        foreach ($this->scandir(true) as $fileName) {
            $file = $this->withPath($fileName);

            if ($file->isReadable()) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * Deletes the current file/directory completely
     *
     * @return void
     */
    public function delete(): void
    {
        $this->assertExistance('Failed to delete');
    
        if ($this->isDir()) {
            foreach ($this->scandir(true) as $child) {
                $this->withPath($child)->delete();
            }

            $this->assert(function () {
                rmdir($this->toString());
            }, 'Failed to delete directory');
        } else {
            $this->assert(function () {
                unlink($this->toString());
            }, 'Failed to delete file');
        }
    }

    /**
     * Copies current file to $destination
     *
     * @param string $destination
     * @return void
     */
    public function copy(string $destination): void
    {
        $this->assertExistance('Failed to copy');
        $destination = new self($destination);
        $destination->assertNotExistance('Failed to copy');

        if ($this->isDir()) {
            $destination->createDirIfNotExists();
            foreach ($this->getChildren() as $child) {
                $child->copy($destination->withPath($child->getName()));
            }
        } else {
            $this->assert(function () use ($destination) {
                return copy($this->toString(), $destination->toString());
            });
        }
    }


    /**
     * Assert that the file exists.
     *
     * @param string $message
     * @return void
     */
    public function assertExistance(string $message = ''): void
    {
        $this->assert([$this, 'exists'], new FileNotFoundException($this->toString(), $message));
    }

    /**
     * Assert that the file does not exists.
     *
     * @param string $message
     * @return void
     */
    public function assertNotExistance(string $message = ''): void
    {
        $this->assert([$this, 'notExists'], new FileAlreadyExistsException($this->toString(), $message));
    }

    /**
     * Assert that the file is directory.
     *
     * @param string $message
     * @return void
     */
    public function assertIsDirectory(string $message = ''): void
    {
        $this->assertExistance($message);
        $this->assert([$this, 'isDir'], new FileTypeException('Directory', $this->toString(), $message));
    }

    /**
     * Assert that the file is a file and not a directory.
     *
     * @param string $message
     * @return void
     */
    public function assertIsFile(string $message = ''): void
    {
        $this->assertExistance($message);
        $this->assert([$this, 'isFile'], new FileTypeException('File', $this->toString(), $message));
    }

    /**
     * Assert that the file is readable.
     *
     * @param string $message
     * @return void
     */
    public function assertReadable(string $message = ''): void
    {
        $this->assertExistance($message);
        $this->assert([$this, 'isReadable'], new FileNotReadableException($this->toString(), $message));
    }

    /**
     * Assert that the file is writeable.
     *
     * @param string $message
     * @return void
     */
    public function assertWriteable(string $message = ''): void
    {
        $this->assertExistance($message);
        $this->assert([$this, 'isWriteable'], new FileNotWriteableException($this->toString(), $message));
    }


    /**
     * Assert that a callback returns not false or throws an PHP Error.
     *
     * @param callable $callback
     * @param null|string|FileException $e
     * @throws FileException if $callback fails
     * @return mixed return value of $callback
     */
    protected function assert(callable $callback, $e = null)
    {
        error_clear_last();
        $rs = @$callback();
        if ($rs === false || error_get_last() != null) {
            $e = $e instanceof FileException ? $e : new FileException($this->toString(), is_string($e) ? $e : '');

            if (error_get_last() != null) {
                $e->setCause(error_get_last()['message']);
            }
            
            throw $e;
        }

        return $rs;
    }
}
