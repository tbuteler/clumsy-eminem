<?php namespace Clumsy\Eminem;

use Illuminate\Support\Facades\Config;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    public function add($file, $filename, $options = array())
    {
        if (!$file instanceof UploadedFile || !$file instanceof File)
        {
            $file = new File($file);
        }

        $defaults = array(
            'association_id'   => null,
            'association_type' => null,
            'position'         => null,
            'allow_multiple'   => false,
        );

        $options = array_merge($defaults, $options);
        extract($options, EXTR_SKIP);

        $i = 1;
        $append = null;
        while (file_exists($this->absolutePath() . '/' . $filename . $append))
        {
            $append = " ($i)";  
            $i++;
        }

        $filename .= $append;
        $mime_type = $file->getMimeType();

        $file->move($this->absolutePath(), $filename);
        
        $path = $this->relativePath() . '/' . $filename;
    
        $media = Media::create(array(
            'path_type' => 'relative',
            'path'      => $path,
            'mime_type' => $mime_type,
        ));

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

            $association = MediaAssociation::create(array(
                'media_id'               => $media->id,
                'media_association_type' => $association_type,
                'media_association_id'   => $association_id,
                'position'               => $position,
            ));

            foreach ((array)$association as $key => $value)
            {
                if ($key !== 'id') $media->$key = $value;
            }
        }

        return $media;
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