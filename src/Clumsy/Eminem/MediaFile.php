<?php namespace Clumsy\Eminem;

use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File as Filesystem;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

class MediaFile {

    protected $file;
    protected $errors;
    protected $validator;

    public $original_filename = null;
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
        $this->original_filename = $this->filename;

        $this->errors = new MessageBag;
    }

    protected function basePath()
    {
        return Config::get('clumsy/eminem::folder');
    }

    protected function relativePath()
    {
        $base = $this->basePath();
        
        $organize = Config::get('clumsy/eminem::organize') ? date('Y') . '/' . date('m') : '';

        return "$base/$organize";
    }

    protected function folderPath()
    {        
        return public_path($this->relativePath());
    }

    protected function move($overwrite = false)
    {
        $extension = \Clumsy\Eminem\Facade::guessExtension($this->filename);

        $name = Str::slug(str_replace(".$extension", '', $this->filename));
        $this->filename = $name.".$extension";

        if (!$overwrite)
        {
            $i = 1;
            while (file_exists($this->folderPath().'/'.$this->filename))
            {
                if (preg_match('/\-\d{1,}$/', $name, $count))
                {
                    $count = substr(head($count), 1);
                    $name = preg_replace('/\-\d{1,}$/', (string)'-'.($count+1), $name);
                }
                else
                {
                    $name .= "-$i";
                }
                $this->filename = $name.".$extension";
                $i++;
            }
        }

        try
        {
            $this->file->move($this->folderPath(), $this->filename);
        }
        catch (FileException $e)
        {
            $error = $e->getMessage();

            if (str_contains($error, 'upload_max_filesize'))
            {
                $this->errors->add('file', trans('clumsy/eminem::all.errors.upload_size', array('filename' => $this->original_filename)));
            }
            elseif (str_contains($error, 'Unable to create'))
            {
                $this->errors->add('file', trans('clumsy/eminem::all.errors.permissions', array('filename' => $this->original_filename)));
            }
            else
            {
                $this->errors->add('file', $error);
            }
        }

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

    public function validate($rules = null)
    {
        if ($rules)
        {
            $validator = Validator::make(
                array('file' => $this->file),
                array('file' => $rules)
            );

            if ($validator->fails())
            {
                $this->errors->merge($validator->messages());
            }
        }

        return $this;
    }

    public function add()
    {
        if ($this->hasErrors())
        {
            return $this;
        }

        return $this->move()->save();
    }

    public function addCopy()
    {
        if ($this->hasErrors())
        {
            return $this;
        }

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
        $this->association = $this->model->bind($options);

        return $this;
    }

    public function hasErrors()
    {
        return !$this->errors->isEmpty();
    }

    public function getErrorMessage()
    {
        return $this->errors->first();
    }
}