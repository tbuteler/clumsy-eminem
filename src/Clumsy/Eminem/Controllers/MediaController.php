<?php namespace Clumsy\Eminem\Controllers;

use Clumsy\Eminem\Models\Media;
use Clumsy\Eminem\Models\MediaAssociation;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\View;
use Clumsy\Eminem\Facade as MediaManager;

class MediaController extends \BaseController {

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

	    $files = Input::file('files');

	    $allow_multiple = filter_var(Input::get('allow_multiple'), FILTER_VALIDATE_BOOLEAN);
	    
	    $results = array();

	    foreach ($files as $file)
	    {
	        $input = '';

	        $media = MediaManager::add($file);

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

			$src = URL::to($media->model->path);

	        $results[] = compact('src', 'input', 'html');
	    }

	    return array(
	        'files' => $results
	    );
	}

	public function unbind($id)
	{
		return MediaAssociation::destroy($id);
	}
}
