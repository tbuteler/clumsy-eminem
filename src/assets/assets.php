<?php

return array(

	'jquery' => array(
		'set'	=> 'footer',
		'path'	=> '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
	),

    'bootstrap' => array(
        'set'   => 'styles',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css',
    ),

    'bootstrap.js' => array(
        'set'   => 'footer',
        'path'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js',
        'req'   => 'jquery',
    ),

    'media-management.css' => array(
        'set'   => 'styles',
        'path'  => 'packages/clumsy/eminem/css/media-management.css',
        'req'   => 'bootstrap',
        'v'     => '4.3',
    ),

    'media-management.js' => array(
        'set'   => 'footer',
        'path'  => 'packages/clumsy/eminem/js/media-management.min.js',
        'req'   => 'bootstrap.js',
        'v'     => '4.3',
    ),
);
