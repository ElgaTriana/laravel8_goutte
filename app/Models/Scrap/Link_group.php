<?php

namespace App\Models\Scrap;

use Illuminate\Database\Eloquent\Model;

class Link_group extends Model
{
    protected $connection="mysql3";
    protected $table="scrap_link_group_kategori";

    public function portal(){
        return $this->belongsTo('App\Models\Scrap\Portal','portal_id');
    }
}
