<?php namespace Clumsy\Eminem\Models;

class MediaAssociation extends \Eloquent {
        
    protected $guarded = array('id');

    public $timestamps = false;

    public function getMeta($key)
    {
        return array_get($this->meta, $key);
    }

    public function getMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = $value ? json_encode($value, true) : null;
    }
}