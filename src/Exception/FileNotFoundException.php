<?php
namespace Nyrados\Utils\File\Exception;

class FileNotFoundException extends FileAccessException
{
    protected function getMessageFormat(): string
    {
        return "File '%s' was not found";
    }
}