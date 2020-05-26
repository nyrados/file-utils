<?php
namespace Nyrados\Utils\File\Exception;

class FileAccessException extends FileException
{
    protected function getMessageFormat(): string
    {
        return "File '%s' is not accessible";
    }
}