<?php

namespace App\Models\Scrap;

use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapParam extends Model
{
	// use SoftDeletes;

    
    protected $connection="mysql3";

    protected $table="scrap_portal_parameter";

    public function kanal(){
        return $this->belongsTo('App\Models\Scrap\Kanal','kanal_id');
    }
    
}
