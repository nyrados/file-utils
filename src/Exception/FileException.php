<?php
namespace Nyrados\Utils\File\Exception;

use Exception;
use Nyrados\Utils\File\File;
use Throwable;

class FileException extends Exception
{

    /** @var File */
    protected $fileTarget;

    public function __construct(File $file, string $message = '', $cause = '')
    {
        $this->file = $file;
        $causeString = ($cause instanceof Throwable) ? $cause->getMessage() : (string) $cause;

        parent::__construct($this->formatMessage($message, $causeString));
    }

    final public function getFileTarget(): File
    {
        return $this->file;
    }

    final public function setCause(string $cause)
    {
        $this->message = $this->formatMessage($this->getMessage(), $cause);
    }

    private function formatMessage(string $message = '', string $cause = ''): string
    {

        $msg = sprintf("Error occured on file '%s'", $this->getFile()->getPath());

        if (!empty($message)) {
            $msg .= ': ' . $message;
        }

        if (!empty($cause)) {
            $msg .= sprintf(", caused by '%s'", $cause); 
        }

        return $msg;
    }
}