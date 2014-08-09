<?php namespace Clumsy\Eminem;

use Illuminate\Support\Facades\Input;

trait MediableTrait {

    public static function boot()
    {
        parent::boot();

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
                    $association_type = \Illuminate\Support\Str::lower(class_basename($model));
                    \Clumsy\Eminem\Facade::bind($media_id, $model->id, $association_type, $attributes);
                }
            }
        });
    }

    public function media()
    {
        return $this->morphToMany('Media', 'media_association')->select(array('media.*', 'position'));
    }

    public static function mediaSlots()
    {
        return false;
    }

    public function hasMedia()
    {
        return (bool)sizeof($this->media);
    }
    
    public function mediaPath($position = null)
    {
        if ($this->hasMedia())
        {
            if ($position)
            {
                $media = $this->media->filter(function($media) use ($position)
                    {
                        return $media->position === $position;
                    })
                    ->first();
            }
            else
            {    
                $media = $this->media->first();
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