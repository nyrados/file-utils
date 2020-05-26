<?php
namespace Nyrados\Utils\File;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class WrapperPath extends Path
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
     * @param Path|string $uri
     */
    public function __construct($uri)
    {
        $this->setUri($uri);
    }

    /**
     * Returns Uri as parseable. 
     * 
     * Use this to safly recieve the uri
     *
     * @return string
     */
    public function getUri(): string
    {
        return (!empty($this->getAuthority()) || $this->getScheme() == 'file') ? $this->getWrapper() : $this->getScheme() . ':' . ($this->isWindowsPath() ? '/' : '') . $this->asAbsolute()->path;
    }

    /**
     * Returns Uri as PHP compatible Wrapper.
     * 
     * Main Reason for this is that PHP dont recognize example:/path/abc as wrapper in files.
     *
     * @return string
     */
    public function getWrapper(): string
    {
        return $this->url['scheme'] . '://' . $this->getAuthority()  . $this->asAbsolute()->path;
    }

    /**
     * Reduces the uri to the path.
     *
     * @return Path
     */
    public function asPath(): Path
    {
        return new Path($this->getPath);
    }

    /**
     * Returns string representaion as string.
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->getWrapper();
    }

    /**
     * Get Uri Scheme
     *
     * @return string
     */
    public function getScheme(): string 
    {
        return $this->url['scheme'];
    }

    /**
     * Change the Current Uri
     *
     * @param string|WrapperPath|UriInterface $uri
     * @return static
     */
    public function withUri($uri)
    {
        $new = clone $this;
        $new->setUri($uri);
        
        return $new;
    }

    /**
     * Changes the current Path.
     *
     * If you use a relative path it will append to your current path.
     * If you use a absolute path it the path will replace the current.
     * If you use a uri it will overwrite the current.
     *
     * @param string|Path $path
     * @param boolean $forceAbsolute
     * @return void
     */
    public function withPath($path, $forceAbsolute = false)
    {
        if(!$this->isUri($path)) {
            return parent::withPath($path);
        }

        return $this->withUri($path);
    }

    /**
     * Returns Authority
     * 
     * Format: [user-info@][host][:port] (empty if nothing is provided)
     *
     * @return string
     */
    public function getAuthority(): string
    {
        if(!isset($this->url['host'])) {
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

    /**
     * Sets Current Uri
     *
     * @param string|Path $uri
     * @return void
     */
    protected function setUri($uri)
    {
        if ($uri instanceof UriInterface) {
            $uri = urldecode((string) $uri);
        }

        if($uri instanceof self) {
            $uri = $uri->getWrapper();
        }

        $asPath = new Path($uri);
        if($asPath->isWindowsPath()) {
            $uri = 'file:/' . $uri;
        }

        $uri = preg_replace('/^file:\/\/(' . FileUtils::REGEX_WINDOWSDRIVE . ')/', 'file:/$1', $uri);

        $this->url = parse_url(preg_replace('/^(' . FileUtils::REGEX_WRAPPERNAME . '):\/\/\/([^\/])/', '$1:/$2', (string) $uri));

        $this->url['scheme'] = $this->url['scheme'] ?? 'file';

        if($this->url === false) {
            throw new InvalidArgumentException('Failed to parse url');
        }

        $this->setPath(isset($this->url['path']) ? $this->url['path'] : '/');
    }

    private function isUri($uri)
    {
        return preg_match('/^' . FileUtils::REGEX_WRAPPERNAME . ':\//', (string) $uri);
    }
}