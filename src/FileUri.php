<?php
namespace Nyrados\Utils\File;

use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class FileUri extends Path
{
    /**
     * Url Segments
     *
     * @see https://www.php.net/manual/en/function.parse-url.php
     * @var array
     */
    private $url;

    /**
     * Create a new instance
     *
     * If you use a path a file scheme will be added
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $uri = trim($uri);
        
        if (!preg_match('/^' . FileUtils::REGEX_WRAPPERNAME . ':/', $uri)) {
            $path = (new Path($uri))->asAbsolute();

            $this->url['scheme'] = 'file';
            $this->path = $path->getPath();

            return;
        }

        $uri = preg_replace('/^file:\/\/(' . FileUtils::REGEX_WINDOWSDRIVE . ')/', 'file:/$1', $uri);
        $uri = preg_replace('/^(' . FileUtils::REGEX_WRAPPERNAME . ':\/)\/\/(.*)/', '$1$2', $uri);

        $this->url = parse_url($uri);
        
        if ($this->url === false) {
            throw new InvalidArgumentException('Failed to parse url');
        }

        $this->path = isset($this->url['path']) ? FileUtils::normalizePath($this->url['path']) : '/';
    }

    /**
     * Returns as Uri
     *
     * @return string
     */
    public function getUri(): string
    {
        $uri = $this->getScheme() . ':';

        $auth = $this->getAuthority();
        if (!empty($auth)) {
            $uri .= '//' . $auth;
        }

        if ($this->isWindowsPath()) {
            $uri .= '/';
        }

        return $uri . $this->getPath();
    }

    /**
     * Returns Uri as PHP compatible Wrapper.
     *
     * Difference between getUri():
     * - For uris without authority three slashes will be used. Example:
     *   getUri():      myWrapper:/my/file
     *   getWrapper():  myWrapper:///my/file
     *
     *   getUri():      file:/C:/Users
     *   getWrapper():  C:/Users
     *
     * Reason for this is that PHP dont recognize myWrapper:/path/abc
     * as Uri in e.g. file_exists()
     *
     * @return string
     */
    public function getWrapper(): string
    {
        $auth = $this->getAuthority();
        if ($this->getScheme() === 'file' && empty($auth)) {
            return $this->getPath();
        }

        $uri = $this->getScheme() . '://' . $auth;

        if ($this->isWindowsPath()) {
            $uri .= '/';
        }

        return $uri . $this->getPath();
    }

    /**
     * Returns string representaion.
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->getWrapper();
    }

    /**
     * Get Uri Scheme.
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->url['scheme'];
    }

    /**
     * Returns Authority
     *
     * Format: [user[:password]@][host][:port] (empty if nothing is provided)
     *
     * @return string
     */
    public function getAuthority(): string
    {
        if (!isset($this->url['host'])) {
            return '';
        }

        $auth = $this->url['host'];

        if (isset($this->url['user'])) {
            $auth = $this->url['user'] . (isset($this->url['pass']) ? ':' . $this->url['pass'] : '') .'@' . $auth;
        }

        if (isset($this->url['port'])) {
            $auth .= ':' . $this->url['port'];
        }

        return $auth;
    }
}
