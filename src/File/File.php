<?php

namespace Clumsy\Eminem\File;

use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File extends BaseFile
{
    public function isValid()
    {
        return true;
    }
}
