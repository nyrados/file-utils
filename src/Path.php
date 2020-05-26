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

    public function __construct($path)
    {
        $this->setPath($path);
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
     * Returns String representation as Path
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->path;
    }

    /**
     * Returns String representation of the class
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Checks if path is positive
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
     * Removes '..' Segments from path.
     *
     * @return void
     */
    public function withoutBackwards()
    {
        $new = clone $this;
        $i = 0;

        $dirs = explode("/", $this->path);  
        $rs = [];

        for ($j = sizeof($dirs) - 1; $j >= 0; --$j) {
            if (trim($dirs[$j]) ==="..") {
                $i++;
            } else {
                if($i > 0){
                    $i--;
                }
                else {
                    $rs[] = $dirs[$j];
                }
            }
        }

        $new->path = implode("/", array_reverse($rs));

        if(empty($new->path) && $this->isAbsolute()) {
            $new->path = $this->getRootDir();
        }
        
        return $new;
    }

    /**
     * Returns Parent and removes '..' Segmenents.
     *
     * @return void
     */
    public function getParent()
    {
        return $this->withPath('..')->withoutBackwards();
    }

    /**
     * Changes the current Path.
     *
     * If you use a relative path it will append to your current path.
     * If you use a absolute path it the path will replace the current.
     * If you use a uri it will NOT recognize it and threats it like a relative path.
     * 
     * 
     * @param string|Path $path
     * @param boolean $forceAbsolute
     * @return static
     */
    public function withPath($path, $forceAbsolute = false)
    {
        $path = new self($path);
        $new = clone $this;
        $new->setPath($path->isAbsolute() || $forceAbsolute ? $path : $this->path . '/' . $path->path);
        return $new;
    }

    /**
     * Transform the current Path to a absolute.
     *
     * @param string $pefix use C:/ for windows devices
     * @return static
     */
    public function asAbsolute(string $pefix = '/')
    {
        $pefix = new self($pefix);
        if ($pefix->isRelative()) {
            throw new InvalidArgumentException('Given Prefix is an relative path');
        }

        $new = clone $this;
        if ($this->isAbsolute()) {
            return $new;
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
     * Checks if path has Windowsformat.
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

    /**
     * Sets & normalizes Current Path
     * 
     * @param string $path
     * @return void
     */
    protected function setPath(string $path)
    {
        if($path instanceof self) {
            $this->path = $path->path;
            return;
        } 

        $this->path = FileUtils::normalizePath($path);
    }

}