<?php

namespace Clumsy\Eminem\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\File as Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Intervention\Image\Facades\Image;

class Media extends Eloquent
{
    protected $table = 'media';

    protected $guarded = ['id'];

    protected $file = null;

    protected static $image_mimes = [
        'jpeg',
        'jpg',
        'png',
        'gif',
        'bmp',
    ];

    public function basePath($path = null)
    {
        return $this->isRouted() ? storage_path('eminem/'.$path) : public_path($path);
    }

    public function baseFolder()
    {
        return config("clumsy.eminem.folder");
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
        if (!$this->file) {
            $this->makeFile();
        }

        return $this->file;
    }

    public function isImage()
    {
        return in_array($this->extension, static::$image_mimes);
    }

    public function isExternal()
    {
        return $this->path_type === 'external';
    }

    public function isRouted()
    {
        return $this->path_type === 'routed';
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
        return route('eminem.media-route', ['media' => $this->path]);
    }

    protected function publicURL()
    {
        return url($this->path);
    }

    public function url()
    {
        if ($this->isExternal()) {
            return $this->path;
        }

        return $this->isRouted() ? $this->routedURL() : $this->publicURL();
    }

    public function previewURL()
    {
        if ($this->isImage()) {
            return $this->url();
        }

        $placeholders = config('clumsy.eminem.placeholder-folder').'/';

        if (file_exists(public_path("{$placeholders}{$this->extension}.png"))) {
            return url("{$placeholders}{$this->extension}.png");
        }

        return url("{$placeholders}unknown.png");
    }

    public function bind(array $options = [])
    {
        $defaults = [
            'association_id'   => null,
            'association_type' => null,
            'position'         => null,
            'allow_multiple'   => false,
        ];

        $options = array_merge($defaults, $options);
        extract($options, EXTR_SKIP);

        if ((int)$association_id !== 0) {

            if (!$allow_multiple) {

                $existing = MediaAssociation::where('media_association_id', $association_id);

                if ($association_type !== null) {
                    $existing->where('media_association_type', $association_type);
                }

                if ($position !== null) {
                    $existing->where('position', $position);
                }

                $existing->delete();
            }

            // Trigger binding events, if any
            $associatedModel = with(new $association_type)->find($association_id);
            if (method_exists($associatedModel, 'onMediaAssociation')) {
                $associatedModel->onMediaAssociation($this, $position);
            }

            return MediaAssociation::create([
                'media_id'               => $this->id,
                'media_association_type' => $association_type,
                'media_association_id'   => $association_id,
                'position'               => $position,
            ]);
        }
    }

    public function rename($newName)
    {
        $newPath = str_replace($this->name, $newName, $this->path);

        Filesystem::move($this->basePath($this->path), $this->basePath($newPath));

        $this->path = $newPath;
        $this->save();

        // In case file was already instantiated, make it again with new path
        $this->makeFile();
    }

    public function getExtensionAttribute()
    {
        if ($this->isExternal()) {
            return Filesystem::extension($this->path);
        }

        return $this->baseFile()->guessExtension();
    }

    public function getNameAttribute()
    {
        return Filesystem::name($this->path);
    }

    public function getNameAndExtensionAttribute()
    {
        $name = $this->name;

        $extension = $this->extension;
        if ($extension) {
            $name .= ".{$extension}";
        }

        return $name;
    }

    public function getMeta($key)
    {
        return array_get($this->meta, $key);
    }

    public function getMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = $value ? json_encode($value, true) : null;
    }

    public function getAssociationMeta($key)
    {
        return array_get($this->association_meta, $key);
    }

    public function getAssociationMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getPivotMeta($key = null)
    {
        if (!is_null($this->pivot)) {
            $meta = json_decode($this->pivot->meta, true);
            return is_null($key) ? $meta : array_get($meta, $key);
        }

        return null;
    }

    public function __toString()
    {
        return $this->url();
    }
}
