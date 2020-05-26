<?php
namespace Nyrados\Utils\File\Exception;

class FileAlreadyExistsException extends FileException
{
    protected function getMessageFormat(): string
    {
        return "File '%s' already exists";
    }
}