<?php 

return array(

	'jquery' => array(
		'set'	=> 'footer',
		'path'	=> '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
	),

    'bootstrap.css' => array(
        'set'   => 'styles',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css',
    ),
	
    'bootstrap.js' => array(
        'set'   => 'footer',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js',
        'req'   => 'jquery',
    ),

    'media-management.css' => array(
        'set'   => 'styles',
        'path'  => 'packages/clumsy/eminem/css/media-management.css',
        'req'   => 'bootstrap.css',
        'v'     => '0.1',
    ),
    
    'media-management.js' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/eminem/js/media-management.min.js',
        'req'   => 'bootstrap.js',
        'v'     => '0.1',
    ),
);