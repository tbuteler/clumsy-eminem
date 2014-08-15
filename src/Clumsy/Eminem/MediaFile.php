<?php namespace Clumsy\Eminem;

use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Str;

class MediaFile {

    public $file = null;
    public $filename = null;
    public $mime_type = null;
    public $model = null;
    public $association = null;

    public function __construct($file, $filename)
    {
        if (!$file instanceof UploadedFile || !$file instanceof File)
        {
            $file = new File($file);
        }
        
        if ($file instanceof UploadedFile)
        {
            $this->filename = !$filename ? $file->getClientOriginalName() : $filename;
            $this->mime_type = $file->getClientMimeType();
        }
        else
        {
            $this->filename = !$filename ? $file->getFilename() : $filename;
            $this->mime_type = $file->getMimeType();
        }

        $this->file = $file;
    
        $this->checkMimeType();
    }

    public function basePath()
    {
        return Config::get('eminem::folder');
    }

    public function relativePath()
    {
        $base = $this->basePath();
        
        $organize = Config::get('eminem::organize') ? date('Y') . '/' . date('m') : '';

        return "$base/$organize";
    }

    public function folderPath()
    {        
        return public_path($this->relativePath());
    }

    protected function checkMimeType()
    {
        /*
        if (!in_array($this->mime_type, Config::get('media.allowed')))
        {
            return Response::make(array(
                'message' => sprintf('You have tried to upload a file that is not currently supported. Please retry with any of the following types of media: %s', implode(', ', $allowed))
            ), 415);
        }
        */
    }

    protected function move($overwrite = false)
    {
        if (!$overwrite)
        {
            $i = 1;
            while (file_exists($this->folderPath().'/'.$this->filename))
            {
                $this->filename .= " ($i)";
                $i++;
            }
        }

        $this->file->move($this->folderPath(), $this->filename);

        return $this;
    }

    protected function save()
    {
        $this->model = Media::create(array(
            'path_type' => 'relative',
            'path'      => $this->relativePath().'/'.$this->filename,
            'mime_type' => $this->mime_type,
        ));

        return $this;
    }

    public function add()
    {
        return $this->move()->save();
    }

    public function addCopy()
    {
        $base = $this->basePath();

        do
        {
            $temp = public_path().'/'.$base.'/'.Str::quickRandom();
        }
        while (Filesystem::isDirectory($temp));

        Filesystem::makeDirectory($temp, 0775, true);

        Filesystem::copy(
            $this->file->getRealPath(),
            $temp.'/'.$this->filename
        );

        $this->file = new File($temp.'/'.$this->filename);

        $this->move()->save();

        Filesystem::deleteDirectory($temp);

        return $this;
    }

    public function bind($options = array())
    {
        if (!$this->model)
        {
            // TODO?: Throw exception
            return $this;
        }

        $this->association = $this->model->bind($options);

        return $this;
    }
}