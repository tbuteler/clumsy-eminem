<?php namespace Clumsy\Eminem\Models;

class MediaAssociation extends \Eloquent {
        
    protected $guarded = array('id');

    public $timestamps = false;

    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = json_encode($value, true);
    }

}