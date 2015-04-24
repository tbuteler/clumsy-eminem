<?php namespace Clumsy\Eminem\Traits;

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
            if (\Illuminate\Support\Facades\Input::has('media_bind'))
            {
                foreach (\Illuminate\Support\Facades\Input::get('media_bind') as $media_id => $attributes)
                {
                    $media = \Clumsy\Eminem\Models\Media::find($media_id);

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
        return $this->morphToMany('\Clumsy\Eminem\Models\Media', 'media_association')
                    ->withPivot('position', 'meta');
    }

    public function mediaSlots()
    {
        return false;
    }

    public function hasMedia()
    {
        return (bool)sizeof($this->media);
    }
    
    public function getMediaByPosition($position = null, $offset = 0)
    {
        $media = $this->media;

        if ($position)
        {
            $media = $media->filter(function($media) use ($position)
                {
                    return $media->pivot->position === $position;
                })
                ->values();
        }

        return $media->offsetExists($offset) ? $media->offsetGet($offset) : null;
    }

    public function mediaPath($position = null, $offset = 0)
    {
        if ($this->hasMedia())
        {
            $media = $this->getMediaByPosition($position, $offset);

            if ($media)
            {
                return $media->path();
            }
        }

        return $this->mediaPlaceholder($position);
    }

    public function mediaMeta($position = null, $offset = 0)
    {
        if ($this->hasMedia())
        {
            $media = $this->getMediaByPosition($position, $offset);

            if ($media)
            {
                return $media->pivot->meta;
            }
        }
    }

    public function mediaPlaceholder($position = null)
    {
        return '';
    }
}