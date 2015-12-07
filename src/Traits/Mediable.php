<?php

namespace Clumsy\Eminem\Traits;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Clumsy\Eminem\Models\MediaAssociation;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Facade as MediaManager;

trait Mediable
{
    public static function bootMediable()
    {
        self::saving(function (Eloquent $model) {

            if (isset($model->files)) {
                unset($model->files);
            }
            if (isset($model->media_bind)) {
                unset($model->media_bind);
            }
            if (isset($model->media_unbind)) {
                unset($model->media_unbind);
            }
        });

        self::saved(function (Eloquent $model) {

            if (request()->has('media_bind')) {
                foreach (request()->get('media_bind') as $media_id => $attributes) {
                    $media = Media::find($media_id);

                    if ($media) {
                        $options = array_merge(
                            [
                                'association_id'   => $model->id,
                                'association_type' => get_class($model),
                            ],
                            $attributes
                        );

                        $media->bind($options);
                    }
                }
            }

            if (request()->has('media_unbind')) {
                foreach (request()->get('media_unbind') as $bind_id) {
                    MediaAssociation::destroy($bind_id);
                }
            }
        });
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_association')
                    ->withPivot('position', 'meta', 'id as bindId');
    }

    public function mediaSlots()
    {
        return false;
    }

    public function getMediaSlot($position)
    {
        return MediaManager::getSlot($this, $position);
    }

    public function addToMediaSlot($position, $file, $filename = null)
    {
        $options = array_merge(
            $this->getMediaSlot($position),
            [
                'association_type' => get_class($this),
                'association_id'   => $this->id,
                'position'         => $position,
            ]
        );

        return MediaManager::add($options, $file, $filename)->bind($options);
    }

    public function addCopyToMediaSlot($position, $file, $filename = null)
    {
        $options = array_merge(
            $this->getMediaSlot($position),
            [
                'association_type' => get_class($this),
                'association_id'   => $this->id,
                'position'         => $position,
            ]
        );

        return MediaManager::addCopy($options, $file, $filename)->bind($options);
    }

    public function mediaBox($position)
    {
        return MediaManager::mediaBox($this, $position);
    }

    public function attachment($position = null, $offset = 0)
    {
        $media = $this->media;

        if ($position) {
            $media = $media->filter(function (Media $media) use ($position) {
                return $media->pivot->position === $position;
            })->values();
        }

        if ($offset === 'all') {
            return $media;
        }

        return $media->offsetExists($offset) ? $media->offsetGet($offset) : null;
    }

    public function allMedia($position = null)
    {
        return $this->attachment($position, 'all');
    }

    public function countMedia($position = null)
    {
        return count($this->attachment($position, 'all'));
    }

    public function hasMedia($position = null)
    {
        return (bool)$this->countMedia($position);
    }

    public function mediaPath($position = null, $offset = 0)
    {
        if ($this->hasMedia($position)) {
            $media = $this->attachment($position, $offset);

            if ($media) {
                return $media->url();
            }
        }

        return $this->mediaPlaceholder($position);
    }

    public function mediaMeta($position = null, $offset = 0)
    {
        if ($this->hasMedia($position)) {
            $media = $this->attachment($position, $offset);

            if ($media) {
                return json_decode($media->pivot->meta, true);
            }
        }
    }

    public function mediaPlaceholder($position = null)
    {
        return '';
    }
}
