<?php

namespace Clumsy\Eminem\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Clumsy\Eminem\Models\Media;

class MediaAssociation extends Eloquent
{
    protected $guarded = ['id'];

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
