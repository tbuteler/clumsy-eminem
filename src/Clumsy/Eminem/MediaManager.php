<?php namespace Clumsy\Eminem;

use Illuminate\Support\Facades\Config;
use Clumsy\Eminem\Models\MediaAssociation;

class MediaManager {

    public function relativePath()
    {
        $base = Config::get('eminem::folder');
        
        $organize = Config::get('eminem::organize') ? date('Y') . '/' . date('m') : '';

        return "/$base/$organize";
    }

    public function absolutePath()
    {        
        return public_path($this->relativePath());
    }

    public function slots($model, $id = null)
    {
        $slots = array();

        $defaults = array(
            'association_type'  => $model,
            'association_id'    => $id,
        );

        $defined = $model::mediaSlots();

        if (is_array($defined))
        {
            if (!is_associative($defined))
            {
                foreach ($defined as $slot)
                {
                    $slots[$slot['position']] = array_merge(
                        $defaults,
                        array('id' => $slot['position']),
                        $slot
                    );
                }
            }
            else
            {
                $slots[$defined['position']] = array_merge(
                    $defaults,
                    array('id' => $defined['position']),
                    $defined
                );
            }
        }

        return $slots;
    }

    public function bind($media_id, $association_id, $association_type, $attributes = array())
    {
        $position = isset($attributes['position']) ? $attributes['position'] : null;

        if (!isset($attributes['allow_multiple']) || !$attributes['allow_multiple'])
        {
            MediaAssociation::where('media_association_type', $association_type)
                            ->where('media_association_id', $association_id)
                            ->where('position', $position)
                            ->delete();
        }

        MediaAssociation::create(array(
            'media_id'               => $media_id,
            'media_association_type' => $association_type,
            'media_association_id'   => $association_id,
            'position'               => $position,
        ));
    }
}