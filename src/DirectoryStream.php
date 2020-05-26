<?php

namespace Nyrados\Utils\File;

use LogicException;

/**
 * Decorator for a directory resource
 */
final class DirectoryStream
{

    /** @var resource */
    private $dir;

    private $ignoreDots = false;

    /**
     * Opens a directory handle.
     *
     * @param File $file
     * @param boolean $ignoreDots
     */
    public function __construct(File $file, $ignoreDots = false)
    {
        $file->assertIsDir();
        $this->dir = opendir($file->toString());
        $this->ignoreDots = $ignoreDots;
    }

    /**
     * Sets the pointer to zero.
     *
     * @return void
     */
    public function rewind(): void
    {
        if (!is_resource($this->dir)) {
            throw new LogicException('Stream is closed');
        }

        rewinddir($this->dir);
    }

    /**
     * Reads next directory entry.
     *
     * @return string|false if no next entry is present, it returns false
     */
    public function read()
    {
        if (!is_resource($this->dir)) {
            throw new LogicException('Stream is closed');
        }

        $dir = readdir($this->dir);

        if ($this->ignoreDots && ($dir == '.' || $dir == '..')) {
            return readdir($this->dir);
        }
        return $dir;
    }

    /**
     * Closes Directory Handle.
     *
     * @return void
     */
    public function close(): void
    {
        closedir($this->dir);
    }

    /**
     * Close Directory Handle on destruct.
     */
    public function __destruct()
    {
        $this->close();
    }
}