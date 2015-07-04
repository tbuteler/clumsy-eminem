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

    public function add($file, $filename = null, $rules = null)
    {
        return with(new MediaFile($file, $filename))
                ->validate($rules)
                ->add();
    }

    public function addCopy($file, $filename = null, $rules = null)
    {
        return with(new MediaFile($file, $filename))
                ->validate($rules)
                ->addCopy();
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
            
            if (!array_is_associative($defined))
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

    public function mediaSlotComments($options = array())
    {
        $output = array();

        $defaults = array(
            'allow_multiple'    => false,
            'validate'          => '',
            'show_comments'     => true,
            'comments'          => '',
        );

        $options = array_merge($defaults, $options);
        extract($options, EXTR_SKIP);

        $show_all = $show_comments && !is_array($show_comments);

        if (!$show_comments)
        {
            return $output;
        }

        if ($comments)
        {
            $output = (array)$comments;
        }

        if ($validate)
        {
            $rules = explode('|', $validate);
            foreach ($rules as $rule)
            {
                if (str_contains($rule, 'image') && ($show_all || isset($show_comments['mimes'])))
                {
                    $types = array('jpeg', 'png', 'gif', 'bmp');
                    $output[] = trans('clumsy/eminem::all.comments.mimes', array('values' => '<strong>'.implode(', ', $types).'</strong>'));
                }

                if (str_contains($rule, 'mimes') && ($show_all || isset($show_comments['mimes'])))
                {
                    list($rule, $types) = explode(':', $rule);
                    $types = explode(',', $types);
                    $output[] = trans('clumsy/eminem::all.comments.mimes', array('values' => '<strong>'.implode(', ', $types).'</strong>'));
                }

                if (str_contains($rule, 'min') && ($show_all || isset($show_comments['min'])))
                {
                    list($rule, $min) = explode(':', $rule);
                    $output[] = trans('clumsy/eminem::all.comments.min', array('min' => '<strong>'.$min.'</strong>'));
                }

                if (str_contains($rule, 'max') && ($show_all || isset($show_comments['max'])))
                {
                    list($rule, $max) = explode(':', $rule);
                    $output[] = trans('clumsy/eminem::all.comments.max', array('max' => '<strong>'.$max.'</strong>'));
                }
            }
        }

        if ($allow_multiple && ($show_all || isset($show_comments['allow_multiple'])))
        {
            $output[] = trans('clumsy/eminem::all.comments.allow_multiple');
        }

        return $output;
    }
}