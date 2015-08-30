<?php namespace Clumsy\Eminem\File;

use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File as BaseFile {

    public function isValid()
    {
        return true;
    }
}