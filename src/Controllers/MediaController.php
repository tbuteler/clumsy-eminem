<?php

namespace Clumsy\Eminem\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Collective\Html\FormFacade as Form;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Clumsy\Eminem\Facade as MediaManager;
use Clumsy\Eminem\Exceptions\IllegalMediaSlotException;

class MediaController extends Controller
{
    public function upload()
    {
        $association = Crypt::decrypt(request()->get('association'));
        list($model, $position) = explode('|', $association);

        if (!$slot = MediaManager::getSlot($model, $position)) {
            throw new IllegalMediaSlotException;
        }

        extract($slot, EXTR_SKIP);

        $results = [];

        foreach (request()->file('files') as $file) {
            $input = '';

            $media = MediaManager::add($slot, $file, null);

            if ($media->hasErrors()) {
                $status = 'error';
                $message = $media->getErrorMessage();
                $results[] = compact('status', 'message');
                continue;
            }

            $status = 'success';

            $media_id = $media->model->id;
            $src = $media->model->url();
            $preview = $media->model->previewURL();

            $input = Form::mediaBind($media_id, $position, $allow_multiple);

            $html_data = [];
            $html_data['media'] = $media->model;
            $html = view('clumsy/eminem::media-item', $html_data)->render();

            $results[] = compact('media_id', 'src', 'preview', 'status', 'input', 'html');
        }

        event('eminem.uploaded', array($results));

        $response = response(['files' => $results]);

        $response->header('Vary', 'Accept');
        $response->header('Content-Type', (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) ? 'application/json' : 'text/plain');

        return $response;
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
}
