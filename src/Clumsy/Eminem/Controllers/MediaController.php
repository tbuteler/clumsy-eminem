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

	public function upload($object = null, $position = null)
	{
		if ($object)
		{
			list($association_id, $association_type) = explode('-', $object);
		}
		else
		{
			$association_type = $association_id = null;
		}

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

	        if ((int)$association_id !== 0)
	        {
				$media->bind(array(
		            'association_id'   => $association_id,
		            'association_type' => $association_type,
		            'position'         => $position,
		            'allow_multiple'   => $allow_multiple,
				));

				$media->model->association_id = $media->association->id;

		        $html = View::make('clumsy/eminem::media-item', array('media' => $media->model))->render();
	        }
	        else
	        {
				$input = Form::mediaBind($media->model->id, $position, $allow_multiple);
	        }

			$src = $media->model->path();

			$preview = $media->model->previewPath();

	        $results[] = compact('status', 'src', 'preview', 'input', 'html');
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
}
