<?php
namespace Nyrados\Utils\File\Exception;

use Exception;
use Nyrados\Utils\File\File;
use Nyrados\Utils\File\Target;
use Throwable;

class FileException extends Exception
{
    protected $filename;
    protected $prefix;

    public function __construct($file, string $messagePrefix = '', $cause = '')
    {
        $this->prefix = $messagePrefix;
        $this->filename = $file instanceof Target ? $file->toString() : $file;
        $causeString = ($cause instanceof Throwable) ? $cause->getMessage() : (string) $cause;

        parent::__construct($this->formatMessage($messagePrefix, $causeString));
    }

    final public function getFilename()
    {
        return $this->filename;
    }

    final public function setCause(string $cause)
    {
        $this->message = $this->formatMessage($this->prefix, $cause);
    }

    protected function getMessageFormat(): string
    {
        return "Error occured file '%s'";
    }

    private function formatMessage(string $prefix = '', string $cause = ''): string
    {        
        $msg = empty($prefix) ? '' : ($prefix .  ': ');
        $msg .= sprintf($this->getMessageFormat(), $this->filename);

        if (!empty($cause)) {
            $msg .= sprintf(", caused by '%s'", $cause); 
        }

        return $msg;
    }
}