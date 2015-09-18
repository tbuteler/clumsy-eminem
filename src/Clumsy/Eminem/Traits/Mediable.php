<?php namespace Clumsy\Eminem\Traits;

trait Mediable {

    public static function bootMediable()
    {
        self::saving(function($model)
        {
            if (isset($model->files)) unset($model->files);
            if (isset($model->media_bind)) unset($model->media_bind);
            if (isset($model->media_unbind)) unset($model->media_unbind);
        });

        self::saved(function($model)
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

            if (\Illuminate\Support\Facades\Input::has('media_unbind'))
            {
                foreach (\Illuminate\Support\Facades\Input::get('media_unbind') as $bind_id)
                {
                    \Clumsy\Eminem\Models\MediaAssociation::destroy($bind_id);
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

    public function getMediaSlot($position)
    {
        return \Clumsy\Eminem\Facade::getSlot($this, $position);
    }

    public function addToMediaSlot($position, $file, $filename = null)
    {
        $options = array_merge(
            $this->getMediaSlot($position),
            array(
                'association_type' => class_basename($this),
                'association_id'   => $this->id,
                'position'         => $position,
            )
        );

        return \Clumsy\Eminem\Facade::add($options, $file, $filename)->bind($options);
    }

    public function addCopyToMediaSlot($position, $file, $filename = null)
    {
        $options = array_merge(
            $this->getMediaSlot($position),
            array(
                'association_type' => class_basename($this),
                'association_id'   => $this->id,
                'position'         => $position,
            )
        );

        return \Clumsy\Eminem\Facade::addCopy($options, $file, $filename)->bind($options);
    }

    public function attachment($position = null, $offset = 0)
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

        if ($offset === 'all')
        {
            return $media;
        }

        return $media->offsetExists($offset) ? $media->offsetGet($offset) : null;
    }

    public function hasMedia($position = null)
    {
        $media = $this->attachment($position, 'all');

        return (bool)sizeof($media);
    }

    public function mediaPath($position = null, $offset = 0)
    {
        if ($this->hasMedia($position))
        {
            $media = $this->attachment($position, $offset);

            if ($media)
            {
                return $media->url();
            }
        }

        return $this->mediaPlaceholder($position);
    }

    public function mediaMeta($position = null, $offset = 0)
    {
        if ($this->hasMedia($position))
        {
            $media = $this->attachment($position, $offset);

            if ($media)
            {
                return json_decode($media->pivot->meta, true);
            }
        }
    }

    public function mediaPlaceholder($position = null)
    {
        return '';
    }
}