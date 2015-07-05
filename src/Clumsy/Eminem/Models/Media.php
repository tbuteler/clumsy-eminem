<?php namespace Clumsy\Eminem\Models;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\File\File;
use Intervention\Image\Facades\Image;
use Clumsy\Eminem\Facade as MediaManager;

class Media extends \Eloquent {
    
    protected $table = 'media';
    
    protected $guarded = array('id');

    protected $file = null;

    public function basePath($path = null)
    {
        return $this->path_type === 'routed' ? storage_path('eminem/'.$path) : public_path($path);
    }

    public function baseFolder()
    {
        return Config::get("clumsy/eminem::{$this->path_type}-folder");
    }

    public function filePath()
    {
        return $this->basePath($this->path);
    }

    protected function baseFile()
    {
        return new File($this->filePath());
    }

    protected function makeFile()
    {
        $this->file = $this->isImage() ? Image::make($this->filePath()) : $this->baseFile();
    }

    public function file()
    {
        if (!$this->file)
        {
            $this->makeFile();
        }

        return $this->file;
    }

    public function isImage()
    {
        return in_array($this->extension, array('jpeg', 'png', 'gif', 'bmp'));        
    }

    public function scopeAssociatedTo($query, $association_id)
    {
        return $query->select(
            'media.*',
            'media_associations.id as association_id',
            'media_associations.meta as association_meta'
        )
        ->join('media_associations', 'media_associations.media_id', '=', 'media.id')
        ->where('media_association_id', $association_id);
    }

    protected function routedURL()
    {
        $route = Config::get('clumsy/eminem::media-route');
        return URL::route($route, array('media' => $this->path));
    }

    protected function publicURL()
    {
        return URL::to($this->path);
    }

    public function url()
    {
        return $this->path_type === 'routed' ? $this->routedURL() : $this->publicURL();
    }

    public function previewURL()
    {
        if ($this->isImage())
        {
            return $this->url();
        }

        $placeholders = Config::get('clumsy/eminem::placeholder-folder').'/';

        if (file_exists(public_path().'/'.$placeholders.$this->extension.'.png'))
        {
            return URL::to($placeholders.$this->extension.'.png');
        }

        return URL::to($placeholders.'unknown.png');
    }

    public function bind($options = array())
    {
        $defaults = array(
            'association_id'   => null,
            'association_type' => null,
            'position'         => null,
            'allow_multiple'   => false,
        );

        $options = array_merge($defaults, $options);
        extract($options, EXTR_SKIP);

        if ((int)$association_id !== 0)
        {
            if (!$allow_multiple)
            {
                $existing = MediaAssociation::where('media_association_id', $association_id);

                if ($association_type !== null)
                {
                    $existing->where('media_association_type', $association_type);
                }

                if ($position !== null)
                {
                    $existing->where('position', $position);
                }
                
                $existing->delete();
            }

            return MediaAssociation::create(array(
                'media_id'               => $this->id,
                'media_association_type' => $association_type,
                'media_association_id'   => $association_id,
                'position'               => $position,
            ));
        }
    }

    public function getExtensionAttribute()
    {
        return $this->baseFile()->guessExtension();
    }

    public function getMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getAssociationMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function __toString()
    {
        return $this->url();
    }
}