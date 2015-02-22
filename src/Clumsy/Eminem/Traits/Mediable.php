<?php namespace Clumsy\Eminem\Traits;

use Clumsy\Eminem\Models\Media;
use Illuminate\Support\Facades\Input;

trait Mediable {

    public static function boot()
    {
        parent::boot();

        self::saving(function($model)
        {
            if (isset($model->files)) unset($model->files);
        });

        self::creating(function($model)
        {
            if (isset($model->media_bind)) unset($model->media_bind);
        });

        self::created(function($model)
        {
            if (Input::has('media_bind'))
            {
                foreach (Input::get('media_bind') as $media_id => $attributes)
                {
                    $media = Media::find($media_id);

                    if ($media)
                    {
                        $options = array_merge(
                            array(
                                'association_id'   => $model->id,
                                'association_type' => class_basename($model),
                            ),
                            $attributes
                        );

                        $media->bind($options);
                    }
                }
            }
        });
    }

    public function media()
    {
        return $this->morphToMany('Media', 'media_association')->select(array('media.*', 'position'));
    }

    public function mediaSlots()
    {
        return false;
    }

    public function hasMedia()
    {
        return (bool)sizeof($this->media);
    }
    
    public function mediaPath($position = null, $offset = 0)
    {
        if ($this->hasMedia())
        {
            if ($position)
            {
                $media = $this->media->filter(function($media) use ($position)
                    {
                        return $media->position === $position;
                    })
                    ->values();

                $media = $media->offsetExists($offset) ? $media->offsetGet($offset) : null;

            }
            else
            {    
                $media = $this->media->offsetExists($offset) ? $this->media->offsetGet($offset) : null;
            }

            if ($media)
            {
                return $media->path();
            }
        }

        return $this->mediaPlaceholder($position);
    }

    public function mediaPlaceholder($position = null)
    {
        return '';
    }
}