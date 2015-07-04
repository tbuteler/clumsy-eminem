<?php namespace Clumsy\Eminem\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Clumsy\Eminem\Facade as MediaManager;

class MediaController extends Controller {

	public function upload($position = null)
	{
		$meta = null;
		$association_type = null;
		$association_id = null;

	    $allow_multiple = filter_var(Input::get('allow_multiple'), FILTER_VALIDATE_BOOLEAN);

	    $results = array();

	    foreach (Input::file('files') as $file)
	    {
	        $input = '';

	        $media = MediaManager::add($file, null, Input::get('validate'));

	        if ($media->hasErrors())
	        {
				$status = 'error';
				$message = $media->getErrorMessage();
				$results[] = compact('status', 'message');
	        	continue;
	        }

	        $status = 'success';

	        $media_id = $media->model->id;
			$src = $media->model->path();
			$preview = $media->model->previewPath();

			$input = Form::mediaBind($media_id, $position, $allow_multiple);

	        $html_data = array();
			$html_data['media'] = $media->model;
	        $html = View::make('clumsy/eminem::media-item', $html_data)->render();

	        $results[] = compact('media_id', 'src', 'preview', 'status', 'input', 'html');
	    }

	    Event::fire('eminem.uploaded', array($results));

		$response = Response::make(array(
	        'files' => $results
	    ), 200);
		
		$response->header('Vary', 'Accept');
		$response->header('Content-Type', (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) ? 'application/json' : 'text/plain');

	    return $response;
	}

	public function unbind($id)
	{
		return MediaAssociation::destroy($id);
	}

	public function meta($id)
	{
		$resource = MediaAssociation::find($id);

		if($resource != null){
			$resource->meta = Input::except('_token');
			$resource->save();
			return array('status' => 'ok');
		}

		return array('status' => 'not ok','msg' => trans('clumsy/eminem::all.errors.general'));
	}
}
