<?php

namespace App\Models\Scrap;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class LinkKategori extends Model {
    protected $connection="mysql3";
    protected $table="scrap_link_kategori";

    // use SoftDeletes;

    public function portal(){
    	return $this->belongsTo('App\Models\Scrap\Portal','portal_id');
    }

}
