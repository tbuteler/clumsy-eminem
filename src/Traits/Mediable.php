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

                    $media = MediaManager::media()->find($media_id);

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

            // Remove our indexes from the request, in case multiple saves are made in the same lifecycle
            request()->replace(request()->except('media_bind', 'media_unbind'));
        });
    }

    protected function emptyMediaObject($position = null)
    {
        $media = MediaManager::media();
        $media->path = $this->mediaPlaceholder($position);
        $media->setRelation('pivot', $this->media()->newPivot(compact('position')));
        return $media;
    }

    public function media()
    {
        return $this->morphToMany(
            MediaManager::mediaModel(),
            'media_association',
            'media_associations',
            'media_association_id',
            'media_id'
        )->withPivot('position', 'meta', 'id as bindId');
    }

    public function getMedia($position = null, $offset = 'all', $placeholder = true)
    {
        $media = $this->media;
        if ($position) {
            $media = $media->filter(function (Media $media) use ($position) {
                return $media->pivot->position === $position;
            })->values();
        }
        if ($offset === 'all') {
            if ($media->isEmpty() && $placeholder) {
                $media->push($this->emptyMediaObject($position));
            }
            return $media;
        }

        if ($media->offsetExists($offset)) {
            $media->offsetGet($offset);
        }

        if ($placeholder) {
            return $this->emptyMediaObject($position);
        }

        return null;
    }

    public function firstMedia($position = null, $placeholder = true)
    {
        return $this->getMedia($position, 'all', $placeholder)->first();
    }

    public function countMedia($position = null)
    {
        return count($this->getMedia($position, 'all', false));
    }

    public function hasMedia($position = null)
    {
        return (bool)$this->countMedia($position);
    }

    public function mediaMeta($position = null, $offset = 0)
    {
        if ($this->hasMedia($position)) {
            $media = $this->media($position, $offset);

            if ($media) {
                return json_decode($media->pivot->meta, true);
            }
        }
    }

    public function mediaPlaceholder($position = null)
    {
        return '';
    }

    public function onMediaAssociation(Media $media, $position)
    {

    }

    public function uploadMediaUrl()
    {
        return property_exists($this, 'uploadMediaRoute') ? route($this->uploadMediaRoute) : route('eminem.upload');
    }

    public function updatedMediaMetaUrl()
    {
        return property_exists($this, 'updatedMediaMetaRoute') ? route($this->updatedMediaMetaRoute) : route('eminem.save-meta');
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
}
