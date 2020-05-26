<?php
namespace Nyrados\Utils\File\Exception;

class FileNotWriteableException extends FileAccessException
{
    protected function getMessageFormat(): string
    {
        return "File '%s' is not readable";
    }   
}