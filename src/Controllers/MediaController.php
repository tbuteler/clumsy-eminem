<?php

namespace Clumsy\Eminem\Controllers;

use Illuminate\Routing\Controller;
use Clumsy\Eminem\MediaManager;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Clumsy\Eminem\Exceptions\IllegalMediaSlotException;

class MediaController extends Controller
{
    protected $eminem;

    public function __construct(MediaManager $eminem)
    {
        $this->eminem = $eminem;
    }

    public function upload($bind = false)
    {
        $association = decrypt(request()->get('association'));
        list($model, $association_id, $position) = explode('|', $association);

        if (!$slot = $this->eminem->getSlot($model, $position)) {
            throw new IllegalMediaSlotException;
        }

        $slot['association_id'] = $association_id;

        extract($slot, EXTR_SKIP);

        $results = [];

        foreach (request()->file('files') as $file) {

            $input = '';

            $media = $this->eminem->add($slot, $file, null);

            if ($media->hasErrors()) {
                $status = 'error';
                $message = $media->getErrorMessage();
                $results[] = compact('status', 'message');
                continue;
            }

            $status = 'success';

            if ($bind) {
                $media->bind($slot);
                $media->model->bindId = $media->association->id;
            }

            $mediaId = $media->model->id;
            $src = $media->model->url();
            $preview = $media->model->previewURL();
            $filename = $media->filename;

            if (!$bind) {
                $input .= view($view_bind, [
                    'mediaId'       => $mediaId,
                    'position'      => $position,
                    'allowMultiple' => $allow_multiple
                ]);
            }

            $html_data = [];
            $html_data['media'] = $media->model;
            $html = view($view_media_item, $html_data)->render();

            $results[] = compact('mediaId', 'src', 'filename', 'preview', 'status', 'input', 'html');
        }

        event('eminem.uploaded', array($results));

        $response = response(['files' => $results]);

        $response->header('Vary', 'Accept');
        $response->header('Content-Type', request()->accepts('application/json') ? 'application/json' : 'text/plain');

        return $response;
    }

    public function uploadAndBind()
    {
        return $this->upload(true);
    }

    public function meta($id)
    {
        $resource = MediaAssociation::find($id);

        if (!is_null($resource)) {
            $resource->meta = request()->except('_token');
            $resource->save();
            return ['status' => 'success'];
        }

        return [
            'status' => 'error',
            'msg'    => trans('clumsy/eminem::all.errors.general')
        ];
    }

    public function outputMedia(Media $media)
    {
        return $this->eminem->response($media);
    }
}
