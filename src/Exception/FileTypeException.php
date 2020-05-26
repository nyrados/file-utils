<?php
namespace Nyrados\Utils\File\Exception;

class FileTypeException extends FileException
{

    private $expected;

    public function __construct(string $expected, $file = '', $messagePrefix = '', $cause = '')
    {
        $this->expected = $expected;
        parent::__construct($file, $messagePrefix, $cause);
    }

    protected function getMessageFormat(): string
    {
        return "Expected file '%s' to be type of '" . $this->expected . "'";
    }
}