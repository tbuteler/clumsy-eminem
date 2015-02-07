<?php namespace Clumsy\Eminem;

use Illuminate\Support\Facades\Config;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManager {

    public function guessExtension($path)
    {
        preg_match('/\.\w{2,4}$/', $path, $extension);

        return str_replace('.', '', head($extension));
    }

    public function add($file, $filename = null)
    {
        return with(new MediaFile($file, $filename))->add();
    }

    public function addCopy($file, $filename = null)
    {
        return with(new MediaFile($file, $filename))->addCopy();
    }

    public function slots($model, $id = null)
    {
        $slots = array();

        if (!method_exists($model, 'mediaSlots'))
        {
            return $slots;
        }

        if (!is_object($model))
        {
            $model = new $model;
        }

        $defined = $model->mediaSlots();

        if (is_array($defined))
        {
            $defaults = array(
                'association_type'  => class_basename($model),
                'association_id'    => $id,
            );
            
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
}