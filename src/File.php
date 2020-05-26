<?php
namespace Nyrados\Utils\File;

use Nyrados\Utils\File\Exception\FileAlreadyExistsException;
use Nyrados\Utils\File\Exception\FileException;
use Nyrados\Utils\File\Exception\FileNotFoundException;
use Nyrados\Utils\File\Exception\FileNotReadableException;
use Nyrados\Utils\File\Exception\FileNotWriteableException;
use Nyrados\Utils\File\Exception\FileTypeException;

/**
 * File Class for a controlled File Access
 * 
 * All Paths and uris will be normalized, see Nyrados\Utils\{Path, WrapperPath}
 * 
 */
final class File extends WrapperPath
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

    /**
     * Returns Files Metadata as array
     *
     * @return void
     */
    public function toArray(): array
    {        
        $data = [
            'uri' => $this->toString(),
            'path' => $this->getPath(),
            'name' => $this->getName(),
            'exist' => $this->exists(),
            
        ];

        if($this->exists()) {
            $data['type'] = $this->isDir() ? 'dir' : 'file';
        }

        return $data;
    }

    /**
     * Returns Parent Directory 
     * 
     * If No Parent Directory is present, it returns the same Directory
     *
     * @return static
     */
    public function getParent()
    {
        return $this->withPath(parent::getParent());
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
            $this->assert(function() use ($mode, $recrusive) {
                mkdir($this->toString(), $mode, $recrusive);
            });
            $this->assertIsDir();
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
            $file = fopen($this->toString(), "w");
            fwrite($file,""); 
            fclose($file);    
            $this->assertIsFile();        
        }
    }



    /**
     * Copies the file
     * 
     * If you use a relative path the parent Directory of this file
     * will be used as current working directory.
     * These copy Statements does the same:
     * 
     * $file = new File('/path/to/my/foo')
     * 
     * $file->copy('bar');
     * $file->copy('/path/to/my/bar');
     *
     * @param string|Path $destination
     * @return void
     */
    public function copy($destination)
    {
        $this->assertExistance('Failed to copy');

        $destination = $this->getParent()->withPath($destination);
        $destination->assertNotExistance('Failed to copy');

        if($this->isDir()) {

            $destination->createDirIfNotExists();
            foreach ($this->getChildren() as $child) {
                $child->copy($destination->withPath($child->getName()));
            }

        } else {

            if ($destination->isFile()) {
                throw new FileAlreadyExistsException($destination->toString());
            }

            $this->assert(function() use ($destination) {
                return copy($this->toString(), $destination->toString());
            });
        }
    }

    /**
     * Deletes the current file completly.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->assertExistance('Failed to delete');
    
        if ($this->isDir()) {
            foreach ($this->getChildren() as $child) {
                $child->delete();
            }
            $this->assert(function() {
                rmdir($this->toString());
            }, 'Failed to delete directory');
        } else {

            $this->assert(function() {
                unlink($this->toString());
            }, 'Failed to delete file'); 
        }
    }

    /**
     * Renames the file
     * 
     * If you use a relative path the parent Directory of this file
     * will be used as current working directory.
     * These rename Statements does the same:
     * 
     * $file = new File('/path/to/my/foo')
     * 
     * $file->rename('bar');
     * $file->rename('/path/to/my/bar');
     *
     * @param string|Path $destination
     * @return void
     */
    public function rename($destination)
    {
        $destination = $this->getParent()->withPath($destination);
        $this->assert(function() use ($destination) {
            rename($this->toString(), $destination->toString());
        });
    }

    /**
     * Opens Resource if file is a directory
     *
     * @param boolean $ignoreDots
     * @return DirectoryStream
     */
    public function openDirStream($ignoreDots = false): DirectoryStream
    {
        $this->assertIsDir('Failed to open directory');
        return new DirectoryStream($this, $ignoreDots);
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

        $this->assertIsFile('Failed to open file');
        return $this->assert(function() use ($mode) {
            return fopen($this->toString(), $mode);
        });
    }

    /**
     * Lists Names of Children if file is a directory.
     *
     * 
     * @see https://www.php.net/manual/en/function.scandir.php
     * 
     * @throws FileException
     * @param boolean $ignoreDots if true ., .. will be removed from the array
     * @return string[]
     */
    public function scandir($ignoreDots = false): array
    {
        $this->assertIsDir();
        $dir = $this->openDirStream($ignoreDots);
        $rs = [];
        while (false !== ($file = $dir->read())) {
            if (!$ignoreDots || $file != '..') {
                $rs[] = $file;
            }
        }
        sort($rs);

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
        foreach ($this->scandir(true) as $file) {
            $file = $this->withPath($file);
            if ($file->isReadable()) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * Returns clone instance with a another Path or Uri.
     * 
     * If you use a relative path it will append to your current path.
     * If you use a absolute path it the path will replace the current.
     * If you use a uri the whole uri will replaced.
     *
     * @param string|Path $path
     * @return static 
     */
    public function get($path)
    {
        return $this->withPath($path);
    }

    /**
     * Details for using var_dump().
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Assert that a callback returns not false or throws an PHP Error.
     *
     * @param callable $callback
     * @param null|string|FileException $e
     * @throws FileException if $callback fails
     * @return void
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

    /**
     * Assert that the file exists.
     *
     * @param string $message
     * @return void
     */
    public function assertExistance(string $message = '')
    {
        $this->assert([$this, 'exists'], new FileNotFoundException($this->toString(), $message));
    }

    /**
     * Assert that the file does not exists.
     *
     * @param string $message
     * @return void
     */
    public function assertNotExistance(string $message = '')
    {
        $this->assert([$this, 'notExists'], new FileAlreadyExistsException($this->toString(), $message));
    }

    /**
     * Assert that the file is directory.
     *
     * @param string $message
     * @return void
     */
    public function assertIsDir(string $message = '')
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
    public function assertIsFile(string $message = '')
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
    public function assertReadable(string $message = '')
    {
        $this->assertExistance($message);
        $this->assert([$this, 'isReadable'], new FileNotReadableException($this->toString(), $message));
    }

    /**
     * Assert that the file writeable.
     *
     * @param string $message
     * @return void
     */
    public function assertWriteable(string $message = '')
    {
        $this->assertExistance($message);
        $this->assert([$this, 'isWriteable'], new FileNotWriteableException($this->toString(), $message));
    }

    
}