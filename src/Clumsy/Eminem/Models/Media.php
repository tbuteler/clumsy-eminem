<?php namespace Clumsy\Eminem\Models;

use Illuminate\Support\Facades\URL;

class Media extends \Eloquent {
    
    protected $table = 'media';
    
    protected $guarded = array('id');

    public function path()
    {
        return $this->path_type === 'absolute' ? $this->path : URL::to($this->path);
    }
}