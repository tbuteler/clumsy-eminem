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
			$mime = $file->getClientMimeType();

			/*
			if (!in_array($mime, Config::get('media.allowed')))
			{
				return Response::make(array(
	        		'message' => sprintf('You have tried to upload a file that is not currently supported. Please retry with any of the following types of media: %s', implode(', ', $allowed))
	        	), 415);
			}
			*/

	        $filename = $file->getClientOriginalName();

	        $i = 1;
	        $append = null;
	        while (file_exists(MediaManager::absolutePath() . '/' . $filename . $append))
	        {
	        	$append = " ($i)";	
	        	$i++;
	        }

			$filename .= $append;

	        $file->move(MediaManager::absolutePath(), $filename);
	        
	        $input = '';
	        $path = MediaManager::relativePath() . '/' . $filename;
	    
	        $media = Media::create(array(
	        	'path_type' => 'relative',
	        	'path'		=> $path,
	        	'mime_type' => $mime,
	        ));

	        $src = URL::to($path);

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

				$ma = MediaAssociation::create(array(
					'media_id'	  			 => $media->id,
					'media_association_type' => $association_type,
					'media_association_id'   => $association_id,
					'position'    			 => $position,
				));

				$media->id = $ma->id;

		        $html = View::make('clumsy/eminem::media-item', compact('media'))->render();
	        }
	        else
	        {
				$input = Form::mediaBind($media->id, $position, $allow_multiple);
	        }

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
