<?php

namespace Clumsy\Eminem\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\File as Filesystem;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use SuperClosure\Serializer;
use SuperClosure\Analyzer\TokenAnalyzer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Media extends Eloquent
{
    protected $table = 'media';

    protected $guarded = ['id'];

    protected $file = null;

    /**
     * Available image manipulation methods
     *
     * @var array
     */
    protected $manipulations = [
        'blur',
        'brightness',
        'colorize',
        'contrast',
        'crop',
        'encode',
        'fill',
        'filter',
        'flip',
        'fit',
        'gamma',
        'greyscale',
        'heighten',
        'insert',
        'invert',
        'limitColors',
        'line',
        'mask',
        'opacity',
        'orientate',
        'pixel',
        'pixelate',
        'rectangle',
        'reset',
        'resize',
        'resizeCanvas',
        'rotate',
        'sharpen',
        'text',
        'trim',
        'widen',
    ];

    /**
     * Available image property methods
     *
     * @var array
     */
    protected $imageProperties = [
        'exif',
        'filesize',
        'height',
        'iptc',
        'mime',
        'pickColor',
        'width',
    ];

    /**
     * History of name and arguments of calls performed on image
     *
     * @var array
     */
    public $calls = [];

    /**
     * Additional properties included in checksum
     *
     * @var array
     */
    public $properties = [];

    /**
     * Mime types which we will consider as image
     *
     * @var array
     */
    protected static $imageMimes = [
        'jpeg',
        'jpg',
        'png',
        'gif',
        'bmp',
    ];

    public function baseFolder()
    {
        return config("clumsy.eminem.folder");
    }

    public function basePath($path = null)
    {
        return $this->isRouted() ? storage_path('eminem/'.$path) : public_path($path);
    }

    public function cachePath($name)
    {
        $parts = array_slice(str_split($name, 2), 0, 2);
        return $this->baseFolder().'/cache/'.implode('/', $parts).'/'.$name;
    }

    public function filePath($path = null)
    {
        $path = $path ?: $this->path;

        return $this->basePath($path);
    }

    protected function baseFile()
    {
        try {
            $file = new File($this->filePath());
        } catch (FileNotFoundException $e) {
            $file = new File(dirname(__DIR__).'/assets/img/placeholder.gif');
        }

        return $file;
    }

    protected function makeImage()
    {
        try {
            $file = Image::make($this->filePath());
        } catch (NotReadableException $e) {
            $file = Image::make(dirname(__DIR__).'/assets/img/placeholder.gif');
        }

        return $file;
    }

    protected function makeFile()
    {
        $this->file = $this->isImage() ? $this->makeImage() : $this->baseFile();
    }

    public function file()
    {
        if (!$this->file) {
            $this->makeFile();
        }

        return $this->file;
    }

    public function getFilename()
    {
        return $this->isImage() ? $this->file()->basename : $this->file()->getFilename();
    }

    public function getExtension()
    {
        if ($this->isExternal()) {
            return Filesystem::extension($this->path);
        }
        return $this->baseFile()->guessExtension();
    }

    public function isImage()
    {
        // Don't rely on filename to guess image extensions -- use Symfony's File::guessExtension() instead
        return in_array($this->extension, static::$imageMimes);
    }

    public function isExternal()
    {
        return $this->path_type === 'external' || starts_with($this->path, 'http');
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
        if ($this->isExternal() || !$this->path) {
            return $this->path;
        }

        if ($this->hasCalls()) {

            $this->setProperty('modified', filemtime($this->filePath()));

            $key = $this->checksum();
            $name = $key.".{$this->extension}";
            $path = $this->cachePath($name);
            $filePath = $this->basePath($path);

            // Check if image was saved before
            if (!Filesystem::exists($filePath)) {

                // Manipulated image
                $this->process();

                // Save to cache folder
                if (!Filesystem::exists(dirname($filePath))) {
                    Filesystem::makeDirectory(dirname($filePath), 0775, true, true);
                }

                $this->file()->save($filePath);
            }

            $this->path = $path;
        }

        return $this->isRouted() ? $this->routedURL() : $this->publicURL();
    }

    public function previewURL()
    {
        if ($this->isImage()) {
            return $this->url();
        }

        $placeholders = config('clumsy.eminem.placeholder-folder');
        $extension = Filesystem::extension($this->getFilename());
        if (Filesystem::exists(public_path("{$placeholders}/{$extension}.png"))) {
            return url("{$placeholders}/{$extension}.png");
        }

        return url("{$placeholders}/unknown.png");
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
        return $this->getExtension();
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

    public function getFilenameAttribute()
    {
        return $this->getFilename();
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

    public function maxDimension($dimension, $value)
    {
        $width = $dimension === 'width' ? $value : null;
        $height = $dimension === 'height' ? $value : null;

        $this->resize($width, $height, function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        return $this;
    }

    public function maxHeight($height)
    {
        return $this->maxDimension('height', $height);
    }

    public function maxWidth($width)
    {
        return $this->maxDimension('width', $width);
    }

    /**
     * Set custom property to be included in checksum
     *
     * @param mixed $key
     * @param mixed $value
     * @return Clumsy\Eminem\Models\Media
     */
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * Returns checksum of current image state
     *
     * @return string
     */
    public function checksum()
    {
        $properties = json_encode($this->properties, true);
        $calls = json_encode($this->getSanitizedCalls(), true);
        return md5($properties.$calls);
    }

    /**
     * Register static call for later use
     *
     * @param  string $name
     * @param  array  $arguments
     * @return void
     */
    protected function registerCall($name, $arguments)
    {
        $this->calls[] = ['name' => $name, 'arguments' => $arguments];
    }

    protected function callIsManipulation($name)
    {
        return in_array($name, $this->manipulations);
    }

    protected function callIsImageProperty($name)
    {
        return in_array($name, $this->imageProperties);
    }

    /**
     * Return unprocessed calls
     *
     * @return array
     */
    protected function getCalls()
    {
        return $this->calls;
    }

    /**
     * Check if image was manipulated
     *
     * @return bool
     */
    protected function hasCalls()
    {
        return count($this->getCalls()) > 0;
    }

    /**
     * Replace Closures in arguments with SerializableClosure
     *
     * @return array
     */
    protected function getSanitizedCalls()
    {
        $calls = $this->getCalls();

        foreach ($calls as $i => $call) {
            foreach ($call['arguments'] as $j => $argument) {
                if (is_a($argument, 'Closure')) {
                    $calls[$i]['arguments'][$j] = $this->serializeClosure($argument);
                }
            }
        }

        return $calls;
    }

    /**
     * Serialize Closure
     *
     * @param  Closure $closure
     * @return string
     */
    protected function serializeClosure(\Closure $closure)
    {
        return with(new Serializer(new TokenAnalyzer()))->serialize($closure);
    }

    /**
     * Process call on current image
     *
     * @param  array $call
     * @return void
     */
    protected function processCall($call)
    {
        $this->file = call_user_func_array([$this->file(), $call['name']], $call['arguments']);
    }

    /**
     * Process all saved image calls
     *
     * @return Clumsy\Eminem\Models\Media
     */
    public function process()
    {
        // process calls on image
        foreach ($this->getCalls() as $call) {
            $this->processCall($call);
        }

        return $this;
    }

    public function __call($name, $arguments)
    {
        if ($this->callIsManipulation($name)) {

            $this->registerCall($name, $arguments);
            return $this;

        } elseif ($this->callIsImageProperty($name)) {

            return $this->file()->$name($arguments);
        }

        return parent::__call($name, $arguments);
    }

    public function __toString()
    {
        return $this->url();
    }
}
