<?php namespace Clumsy\Eminem\Models;

class Media extends \Eloquent {
    
    protected $table = 'media';
    
    protected $guarded = array('id');

    public function path()
    {
        return $this->path_type === 'absolute' ? $this->path : URL::to($this->path);
    }
}