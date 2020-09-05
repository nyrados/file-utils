<?php
namespace Nyrados\Utils\File;

use InvalidArgumentException;
use LogicException;

class Path
{

    /**
     * The Path
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new Path an normalize it.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = FileUtils::normalizePath($path);
    }

    /**
     * Returns current Path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns String representation of the Path
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->path;
    }

    /**
     * Returns String representation of the Path
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Checks if path is absolute
     *
     * @return boolean
     */
    public function isAbsolute(): bool
    {
        return preg_match('/' . FileUtils::REGEX_IS_ABSOLUTE . '/', $this->path) === 1;
    }

    /**
     * Checks if Path is relative
     *
     * @return boolean
     */
    public function isRelative(): bool
    {
        return !$this->isAbsolute();
    }


    /**
     * Returns Parent
     *
     * @return static
     */
    public function getParent(): self
    {
        $new = clone $this;

        if (!$this->isRoot()) {
            $new->path = dirname($this->path);
        }

        return $new;
    }

    /**
     * Changes the current Path.
     *
     * If you use a relative path it will append to your current path.
     * If you use a absolute path it the path will replace the current.
     *
     *
     * @param string $path
     * @return static
     */
    public function withPath(string $path): self
    {
        $path = new self($path);
        $new = clone $this;

        $path = new self($path);

        if ($path->isAbsolute()) {
            $new->path = $path->path;
        } else {
            $new->path = $this->isRoot() ? $this->path . $path->path : $this->path . '/' . $path->path;
        }

        return $new;
    }

    /**
     * Transform the current Path to a absolute.
     *
     * @param string $pefix e.g. use C:/ for Windows paths
     * @return static
     */
    public function asAbsolute(string $pefix = '/')
    {
        $new = clone $this;
        if ($this->isAbsolute()) {
            return $new;
        }

        $pefix = new self($pefix);
        if ($pefix->isRelative()) {
            throw new InvalidArgumentException('Given Prefix is an relative path');
        }

        $new->path = $pefix->getPath() . ($pefix->isRoot() ? '' : '/') . $new->path;

        return $new;
    }

    /**
     * Transform the current path to a relative format.
     *
     * @return static
     */
    public function asRelative()
    {
        $new = clone $this;
        if (!$this->isRelative()) {
            $new->path = substr($new->path, $this->isWindowsPath() ? 3 : 1);
        }

        return $new;
    }

    /**
     * Checks if path has Windows Drive at start.
     *
     * @return boolean
     */
    public function isWindowsPath(): bool
    {
        return preg_match('/^[a-zA-Z]:/', $this->path) === 1;
    }

    /**
     * Checks if path is a root Directory.
     *
     * @return boolean
     */
    public function isRoot(): bool
    {
        return preg_match('/' . FileUtils::REGEX_IS_ROOT . '/', $this->path) === 1;
    }

    /**
     * Gets Rootdirectory.
     *
     * Mostly internal, but useful for windows paths.
     *
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->isWindowsPath() ? preg_replace('/^(' . FileUtils::REGEX_WINDOWSDRIVE . '\/)(.*)/', '$1', $this->path) : '/';
    }

    /**
     * Returns the basename of the Path.
     *
     * @return string
     */
    public function getName(): string
    {
        return basename($this->getPath());
    }
}
